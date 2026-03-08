<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ArabicTextService;

class ProcessPurchaseNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $purchaseId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($purchaseId)
    {
        $this->purchaseId = $purchaseId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WhatsAppService $whatsappService)
    {
        try {
            $purchase = Purchase::with(['store', 'supplier', 'items.product', 'items.unit', 'user', 'currency'])->find($this->purchaseId);

            if (!$purchase) {
                Log::error("ProcessPurchaseNotifications: Purchase #{$this->purchaseId} not found.");
                return;
            }

            $store = $purchase->store;
            $user = $purchase->user;
            
            // ============================================================
            // 1. PDF Generation
            // ============================================================
            $pdfData = ['success' => false, 'url' => null, 'filename' => null];
            
            try {
                $arabicService = new ArabicTextService();
                $pdf = Pdf::loadView('store_owner.purchases.invoice_pdf', compact('purchase', 'store', 'arabicService'))
                    ->setPaper('a4', 'portrait')
                    ->setOptions([
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true,
                        'defaultFont' => 'DejaVu Sans'
                    ]);
                
                $filename = 'purchase_' . $purchase->id . '_' . date('Ymd_His') . '.pdf';
                $path = public_path('temp_reports');
                
                if (!file_exists($path)) {
                    @mkdir($path, 0777, true);
                }
                
                // Cleanup old files
                foreach (glob($path . '/*.pdf') as $file) {
                    if (filemtime($file) < time() - 3600) { 
                        @unlink($file); 
                    }
                }

                $pdf->save($path . '/' . $filename);
                
                $pdfData = [
                    'success' => true,
                    'url' => asset('temp_reports/' . $filename),
                    'filename' => $filename
                ];

            } catch (\Exception $e) {
                Log::error("Background Purchase PDF Error: " . $e->getMessage());
            }

            // ============================================================
            // 2. Prepare Data
            // ============================================================
            $netTotal = $purchase->grand_total;
            $totalPaid = $purchase->paid_amount;
            $due = $purchase->grand_total - $purchase->paid_amount;
            $isCredit = ($due > 0);
            
            $sup = $purchase->supplier;
            $supplierName = $sup ? ($sup->contact_name ?? $sup->company_name ?? 'مورد عام') : 'مورد عام';
            $supplierName = mb_convert_encoding($supplierName, 'UTF-8', 'UTF-8');

            $baseCurrency = \App\Models\Currency::find($store->base_currency_id);
            $foreignCurrency = $purchase->currency;
            $exchangeRate = (float)($purchase->exchange_rate ?: 1);
            $hasForeign = ($foreignCurrency && $baseCurrency && $foreignCurrency->id != $baseCurrency->id && $exchangeRate != 1);
            
            $currencyIcon = $foreignCurrency ? ($foreignCurrency->symbol ?? $foreignCurrency->code) : (optional($baseCurrency)->symbol ?? 'TRY');
            $baseIcon = optional($baseCurrency)->symbol ?? 'TRY';

            // ============================================================
            // 3. WhatsApp Notification (To Store Owner)
            // ============================================================
            if ($store->notify_whatsapp && $store->phone_number && $store->wa_notify_purchases) {
                try {
                    $waSend = false;
                    if ($store->wa_purchases_credit_only) {
                        if ($isCredit && $due >= $store->wa_purchases_credit_min) $waSend = true;
                    } else {
                        if ($netTotal >= $store->wa_purchases_min) $waSend = true;
                        if ($isCredit && $due >= $store->wa_purchases_credit_min) $waSend = true;
                    }

                    if ($waSend) {
                        $msg = "🚛 *فاتورة مشتريات جديدة #{$purchase->invoice_number}*\n";
                        $msg .= "👤 المورد: {$supplierName}\n";
                        
                        if ($hasForeign) {
                            $msg .= "💰 القيمة: " . number_format($netTotal, 2) . " {$foreignCurrency->code}\n";
                            $msg .= "🔄 المعادل: " . number_format($purchase->grand_total_in_base_currency, 2) . " {$baseCurrency->code}\n";
                            $msg .= "📈 سعر الصرف: " . (1* $exchangeRate == (int)($exchangeRate) ? number_format($exchangeRate, 0) : number_format($exchangeRate, 2)) . "\n";
                        } else {
                            $msg .= "💰 القيمة: " . number_format($netTotal, 2) . " " . ($foreignCurrency ? $foreignCurrency->code : optional($baseCurrency)->code) . "\n";
                        }

                        if($isCredit) {
                            $dueAmount = $purchase->grand_total_in_base_currency - $totalPaid;
                            $msg .= "❗️ آجل (دين): " . number_format($dueAmount, 2) . " " . optional($baseCurrency)->code . "\n";
                        }
                        $userName = $user ? $user->name : 'غير معروف';
                        $msg .= "✍️ بواسطة: {$userName}";

                        if ($pdfData['success']) {
                            $whatsappService->sendFile($store->phone_number, $pdfData['url'], $msg, $store->id, $pdfData['filename']);
                        } else {
                            $whatsappService->send($store->phone_number, $msg, $store->id);
                        }
                    }
                } catch (\Exception $e) {
                        Log::error("Background Purchase WhatsApp Error: " . $e->getMessage());
                }
            }

            // ============================================================
            // 4. Email Notification (To Store Owner)
            // ============================================================
            if ($store->notify_email && $store->email && $store->email_notify_purchases) {
                try {
                    $emailSend = false;
                    if ($store->email_purchases_credit_only) {
                        if ($isCredit && $due >= $store->email_purchases_credit_min) $emailSend = true;
                    } else {
                        if ($netTotal >= $store->email_purchases_min) $emailSend = true;
                        if ($isCredit && $due >= $store->email_purchases_credit_min) $emailSend = true;
                    }

                    if ($emailSend) {
                        $emailMsg = "تم تسجيل فاتورة مشتريات جديدة.\n\n";
                        $emailMsg .= "رقم الفاتورة: #{$purchase->invoice_number}\n";
                        $emailMsg .= "المورد: {$supplierName}\n";
                        
                        if ($hasForeign) {
                            $emailMsg .= "الإجمالي: " . number_format($netTotal, 2) . " {$foreignCurrency->code}\n";
                            $emailMsg .= "المعادل بالعملة الأساسية: " . number_format($purchase->grand_total_in_base_currency, 2) . " {$baseCurrency->code}\n";
                            $emailMsg .= "سعر الصرف: " . $exchangeRate . "\n";
                        } else {
                            $emailMsg .= "الإجمالي: " . number_format($netTotal, 2) . " " . ($foreignCurrency ? $foreignCurrency->code : optional($baseCurrency)->code) . "\n";
                        }

                        $emailMsg .= "المدفوع: " . number_format($totalPaid, 2) . " " . optional($baseCurrency)->code . "\n";
                        $dueAmount = $purchase->grand_total_in_base_currency - $totalPaid;
                        $emailMsg .= "المتبقي (آجل): " . number_format($dueAmount, 2) . " " . optional($baseCurrency)->code . "\n";
                        $userName = $user ? $user->name : 'غير معروف';
                        $emailMsg .= "بواسطة: {$userName}";

                        Mail::raw($emailMsg, function($m) use ($store, $purchase, $pdfData) {
                            $m->to($store->email)->subject("فاتورة شراء #{$purchase->invoice_number}");
                            
                            if ($pdfData['success']) {
                                $m->attach(public_path('temp_reports/' . $pdfData['filename']));
                            }
                        });
                    }
                } catch (\Exception $e) {
                    Log::error("Background Purchase Email Error: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            Log::error("ProcessPurchaseNotifications Job Failed: " . $e->getMessage());
        }
    }
}
