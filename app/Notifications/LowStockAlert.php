<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable;

    protected $product;

    public function __construct($product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return ['database']; // 👈 الحفظ في قاعدة البيانات فقط
    }

    public function toArray($notifiable)
    {
        return [
            'product_id' => $this->product->id,
            'message' => "تنبيه: المخزون منخفض للمنتج '{$this->product->name_ar}' (المتبقي: {$this->product->stock_quantity})",
            'link' => route('store.products.edit', $this->product->id),
        ];
    }
}