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
    public function handle(WhatsAppService $whatsappService, \App\Services\InventoryService $inventoryService)
    {
        try {
            // Re-fetch sale with relations to ensure fresh data
            $sale = Sale::with(['store', 'contact', 'items.product.baseUnit', 'user', 'items.product.recipes'])->find($this->saleId);

            if (!$sale) {
                Log::error("ProcessSaleNotifications: Sale #{$this->saleId} not found.");
                return;
            }

            // ============================================================
            // 0. Stock Deduction & Cost Calculation (Moved to Background)
            // ============================================================
            Log::info("Starting Stock Deduction for Sale #{$this->saleId}");
            foreach ($sale->items as $item) {
                $product = $item->product; 
                if ($product && $product->track_stock) {
                    try {
                        // Calculate quantity to deduct (considering unit factor)
                        $factor = 1;
                        if ($item->unit_id) {
                            $unit = \App\Models\ProductUnit::find($item->unit_id);
                            if ($unit) $factor = ($unit->is_base_unit || $unit->id == $product->base_unit_id) ? 1 : $unit->conversion_factor;
                        }
                        $qtyToDeduct = $item->quantity * $factor;

                        // Deduct stock and get actual cost (FIFO / Recipe)
                        $actualCost = $inventoryService->reduceStock($product, $qtyToDeduct);
                        
                        // Update item cost in database
                        $item->cost = $actualCost;
                        $item->save();

                    } catch (\Exception $e) {
                         Log::error("Stock Deduction Error (Item {$item->id}): " . $e->getMessage());
                    }
                }
            }
            Log::info("Stock Deduction Completed for Sale #{$this->saleId}");

            // Reload items to get updated costs if needed for reports? 
            // Actually, we already updated the models in memory? No, reduceStock updates Product, we updated SaleItem.
            // We might need to refresh $sale->items if the PDF view relies on exact cost, but usually it relies on price/total.
            // However, stock amounts listed in the alert section below need fresh product data.
            $sale->refresh(); // Refresh sale to get any deep changes if needed
            $items = $sale->items; // Refresh items collection with new data
            
            // ... Rest of the logic ...

            $store = $sale->store;
            //$items = $sale->items; // Already set above
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
                Log::info("Sale #{$this->saleId}: Starting PDF Generation...");
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
                Log::info("Sale #{$this->saleId}: PDF Generated Successfully at $pdfUrl");

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
            
            // ... (Stock Logic) ...

            foreach ($items as $item) {
                 // ... (existing loop) ...
                 $prod = $item->product; 
                 if ($prod && $prod->track_stock) {
                    $currentStock = (float)$prod->current_stock;
                    $alertLimit = (float)$prod->alert_quantity;
                    if ($currentStock <= $alertLimit) {
                         // ... logic ...
                         $stockAlertLines[] = "Alert..."; 
                         $emailAlertData[] = ['name' => $prod->name_ar, 'stock' => $currentStock];
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
                            $waMsg .= "✅ المدفوع: " . ($sale->paid ?? 0) . "\n";
                            $waMsg .= "👤 العميل: " . ($sale->contact ? $sale->contact->contact_name : 'نقدي') . "\n";
                            if ($isCredit) $waMsg .= "⚠️ متبقي عليه: {$sale->due}\n";
                            $waMsg .= "\n📞 للتواصل معنا واتساب: " . ($store->phone_number ?? '-') . "\n";
                            $waMsg .= "شكراً لتعاملكم معنا 🙏\n";
                        } else {
                             Log::info("Sale #{$this->saleId}: WhatsApp skipped (Criteria not met)");
                        }
                    }

                    if ($store->wa_notify_stock && !empty($stockBody)) {
                        $waMsg .= "\n" . $stockBody . "\n";
                    }
                    
                    if (!empty($waMsg)) {
                        Log::info("Sale #{$this->saleId}: Sending WhatsApp...");
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
                        Log::info("Sale #{$this->saleId}: WhatsApp Send Function Called.");
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
