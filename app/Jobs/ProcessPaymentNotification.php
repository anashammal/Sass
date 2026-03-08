<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Payment;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class ProcessPaymentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $paymentId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WhatsAppService $whatsappService)
    {
        try {
            $payment = Payment::with(['store', 'contact', 'sale', 'currency'])->find($this->paymentId);

            if (!$payment) {
                Log::error("ProcessPaymentNotification: Payment #{$this->paymentId} not found.");
                return;
            }

            $store = $payment->store;
            $contact = $payment->contact;
            $sale = $payment->sale;

            // نرسل الإشعار فقط إذا كان العميل لديه رقم هاتف وإعدادات المتجر تسمح بـ WhatsApp
            if ($store->notify_whatsapp && $store->phone_number && $contact && $contact->phone) {
                $baseCurrency = \App\Models\Currency::find($store->base_currency_id);
                $hasForeign = ($payment->currency_id && $payment->currency_id != $store->base_currency_id && $payment->amount_in_foreign_currency > 0);

                $msg = "✅ *تم استلام دفعة مالية جديدة*\n";
                $msg .= "--------------------------\n";
                $msg .= "👤 العميل: " . ($contact->contact_name ?? '-') . "\n";
                
                if ($hasForeign) {
                    $msg .= "💰 المبلغ: " . number_format($payment->amount_in_foreign_currency, 2) . " " . $payment->currency->code . "\n";
                    $msg .= "🔄 المعادل: " . number_format($payment->amount, 2) . " " . optional($baseCurrency)->code . "\n";
                    $msg .= "📈 سعر الصرف: " . $payment->exchange_rate . "\n";
                } else {
                    $msg .= "💰 المبلغ: " . number_format($payment->amount, 2) . " " . (optional($payment->currency)->code ?: optional($baseCurrency)->code) . "\n";
                }

                $msg .= "📅 التاريخ: " . ($payment->payment_date ? $payment->payment_date->format('Y-m-d') : $payment->created_at->format('Y-m-d')) . "\n";
                $msg .= "💳 الطريقة: " . ($payment->method == 'cash' ? 'نقدي' : ($payment->method == 'card' ? 'شبكة' : 'تحويل')) . "\n";
                
                if ($sale) {
                    $msg .= "🧾 مربوطة بفاتورة ر قم #{$sale->id}\n";
                    if ($sale->due > 0) {
                        $msg .= "⚠️ المتبقي في الفاتورة: " . number_format($sale->due, 2) . " " . optional($baseCurrency)->code . "\n";
                    }
                }

                if ($contact->balance != 0) {
                     $balanceType = $contact->balance < 0 ? 'متبقي عليه' : 'رصيد له';
                     $msg .= "📊 إجمالي الحساب: " . abs(number_format($contact->balance, 2)) . " " . optional($baseCurrency)->code . " ({$balanceType})\n";
                }

                $msg .= "--------------------------\n";
                $msg .= "📞 للتواصل معنا واتساب: " . ($store->phone_number ?? '-') . "\n";
                $msg .= "شكرًا لتعاملكم معنا 🙏\n";
                $msg .= "*" . $store->name . "*";

                Log::info("Sending Payment Notification for Payment #{$this->paymentId} to {$contact->phone}");
                $whatsappService->send($contact->phone, $msg, $store->id);
            }

        } catch (\Exception $e) {
            Log::error("ProcessPaymentNotification Job Failed: " . $e->getMessage());
        }
    }
}
