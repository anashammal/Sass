<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StockAlertMail extends Mailable
{
    use Queueable, SerializesModels;
    public $alertLines;
    public $storeName;
    public $reason;

    public function __construct($alertLines, $storeName, $reason = '') {
        $this->alertLines = $alertLines;
        $this->storeName = $storeName;
        $this->reason = $reason;
    }

    public function build() {
        return $this->subject('🚨 تنبيه انخفاض المخزون - ' . $this->storeName)
                   ->view('emails.stock_alert');
    }
}
