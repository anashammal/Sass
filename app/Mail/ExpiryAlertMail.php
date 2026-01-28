<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpiryAlertMail extends Mailable
{
    use Queueable, SerializesModels;
    public $batches;

    public function __construct($batches) { $this->batches = $batches; }

    public function build() {
        return $this->subject('🚨 تنبيه منتجات قاربت على الانتهاء')->view('emails.expiry_alert');
    }
}