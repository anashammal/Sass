<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\WhatsAppService;
use App\Mail\ReportMail;
use App\Mail\StockAlertMail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ArabicTextService;

class ProcessSaleNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $saleId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($saleId)
    {
        $this->saleId = $saleId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WhatsAppService $whatsappService)
    {
        try {
            // Re-fetch sale with relations to ensure fresh data
            $sale = Sale::with(['store', 'contact', 'items.product.baseUnit', 'user'])->find($this->saleId);

            if (!$sale) {
                Log::error("ProcessSaleNotifications: Sale #{$this->saleId} not found.");
                return;
            }

            $store = $sale->store;
            $items = $sale->items;
            $isWithdrawal = $sale->is_withdrawal;

            // ============================================================
            // 1. PDF Generation
            // ============================================================
            $pdfPath = null;
            $pdfUrl = null;
            $pdfName = 'invoice_' . $sale->id . '.pdf';

            // Debug Logging
            Log::info("Job Started for Sale #{$this->saleId}. Email: {$store->notify_email}, WhatsApp: {$store->notify_whatsapp}");

            try {
                $arabicService = new ArabicTextService();
                $pdf = Pdf::loadView('store_owner.pos.invoice_pdf', compact('sale', 'store', 'arabicService'))
                    ->setPaper('a4', 'portrait')
                    ->setOptions([
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true, // Changed to true for background processing if needed
                        'defaultFont' => 'DejaVu Sans'
                    ]);
                
                $tempDir = public_path('temp_reports');
                if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);
                
                $pdfPath = $tempDir . '/' . $pdfName;
                $pdf->save($pdfPath);
                $pdfUrl = asset('temp_reports/' . $pdfName);

            } catch (\Exception $pdfEx) {
                Log::error("Background Invoice PDF Error: " . $pdfEx->getMessage());
            }

            // ============================================================
            // 2. Prepare Data
            // ============================================================
            $netTotal = $sale->total;
            $isCredit = ($sale->due > 0);
            $stockAlertLines = [];
            $emailAlertData = [];

            foreach ($items as $item) {
                $prod = $item->product; 
                if ($prod && $prod->track_stock) {
                    $currentStock = (float)$prod->current_stock; 
                    $alertLimit = (float)$prod->alert_quantity;
                    
                    if ($currentStock <= $alertLimit) {
                        $barcode = $prod->baseUnit ? $prod->baseUnit->barcode : $prod->sku; 
                        $barcodeStr = $barcode ? $barcode : '---';
                        
                        $header = $currentStock <= 0 ? "🔴 نفذت الكمية" : "⚠️ مخزون منخفض";
                        $msgSuffix = $isWithdrawal ? " (بسبب سحب صاحب المتجر)" : "";
                        
                        $stockAlertLines[] = "{$header}{$msgSuffix}\n📦 {$prod->name_ar}\n🔢 {$barcodeStr}\n📉 الحالية: {$currentStock}";
                        
                        $emailAlertData[] = [
                            'name' => $prod->name_ar,
                            'stock' => $currentStock
                        ];
                    }
                }
            }
            
            $stockBody = !empty($stockAlertLines) ? implode("\n", $stockAlertLines) : "";

            // ============================================================
            // 3. WhatsApp Notification
            // ============================================================
            if ($store->notify_whatsapp && $store->phone_number) {
                Log::info("Entering WhatsApp Block for Sale #{$this->saleId}");
                try {
                    $waMsg = "";
                    
                    if (!$isWithdrawal && $store->wa_notify_sales) {
                        $sendInv = false;
                        if ($store->wa_sales_credit_only) { 
                            if ($isCredit && $sale->due >= ($store->wa_sales_credit_min ?? 0)) $sendInv = true; 
                        } else { 
                            if ($netTotal >= ($store->wa_sales_min ?? 0)) $sendInv = true; 
                            if ($isCredit && $sale->due >= ($store->wa_sales_credit_min ?? 0)) $sendInv = true; 
                        }

                        if ($sendInv) {
                            $waMsg .= "🧾 *فاتورة جديدة #{$sale->id}*\n";
                            $waMsg .= "💰 القيمة: {$netTotal}\n";
                            $waMsg .= "👤 العميل: " . ($sale->contact ? $sale->contact->contact_name : 'نقدي') . "\n";
                            if ($isCredit) $waMsg .= "⚠️ متبقي عليه: {$sale->due}\n";
                        }
                    }

                    if ($store->wa_notify_stock && !empty($stockBody)) {
                        $waMsg .= "\n" . $stockBody . "\n";
                    }
                    
                    if (!empty($waMsg)) {
                        if ($pdfUrl) {
                            $whatsappService->sendFile(
                                $store->phone_number, 
                                $pdfUrl, 
                                trim($waMsg), 
                                $store->id,
                                $pdfName 
                            );
                        } else {
                            $whatsappService->send(
                                $store->phone_number, 
                                trim($waMsg), 
                                $store->id 
                            );
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Background WhatsApp Error: " . $e->getMessage());
                }
            }

            // ============================================================
            // 4. Email Notification
            // ============================================================
            if ($store->notify_email && $store->email) {
                Log::info("Entering Email Block for Sale #{$this->saleId}");
                try {
                    // Invoice Email
                    if (!$isWithdrawal && $pdfPath && file_exists($pdfPath)) {
                        $subject = "فاتورة مبيعات جديدة #{$sale->id}";
                        $body = "مرفق طيه فاتورة المبيعات رقم #{$sale->id}.\nالقيمة الإجمالية: {$netTotal}";
                        
                        Mail::to($store->email)->send(new ReportMail(
                            $subject,
                            $body,
                            $pdfPath,
                            $pdfName
                        ));
                    }

                    // Stock Alert Email
                    if ($store->email_notify_stock && !empty($emailAlertData)) {
                        $reason = $isWithdrawal ? "سحب كمية من قبل صاحب المتجر" : "عملية بيع جديدة";
                        Mail::to($store->email)->send(new StockAlertMail($emailAlertData, $store->name, $reason));
                    }
                } catch (\Exception $e) {
                     Log::error("Background Email Error: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            Log::error("ProcessSaleNotifications Job Failed: " . $e->getMessage());
        }
    }
}
