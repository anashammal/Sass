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
                   ->from(config('mail.from.address'), config('mail.from.name'))
                   ->view('emails.stock_alert')
                   ->withSwiftMessage(function ($message) {
                       $domain = 'tech-sys.online';
                       $message->getHeaders()->addTextHeader('List-Unsubscribe', '<mailto:noreply@'.$domain.'?subject=unsubscribe>');
                       $message->getHeaders()->addTextHeader('List-ID', '<alerts.'.$domain.'>');
                       $message->getHeaders()->addTextHeader('Precedence', 'bulk');
                       
                       // Fix Message-ID to avoid @localhost
                       $messageId = md5(uniqid()) . '@' . $domain;
                       $message->getHeaders()->addTextHeader('Message-ID', '<' . $messageId . '>');
                   });
    }
}
