<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreStatusChanged extends Notification
{
    use Queueable;

    protected $newStatus;
    protected $reason;

    public function __construct($newStatus, $reason)
    {
        $this->newStatus = $newStatus;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $subject = $this->newStatus == 'active' ? 'تمت إعادة تفعيل متجرك!' : 'تم إيقاف متجرك مؤقتاً';
        $statusText = $this->newStatus == 'active' ? 'فعال' : 'متوقف مؤقتاً';

        return (new MailMessage)
                    ->subject($subject)
                    ->line('مرحباً ' . $notifiable->name . ',')
                    ->line('نود إعلامك بأنه تم تغيير حالة متجرك إلى: **' . $statusText . '**')
                    ->line('**السبب:** ' . $this->reason)
                    ->line('شكراً لك.');
    }
}
