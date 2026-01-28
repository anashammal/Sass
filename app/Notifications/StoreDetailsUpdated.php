<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreDetailsUpdated extends Notification
{
    use Queueable;

    // سنقوم بتخزين التغييرات لإظهارها في الإيميل
    protected $changes;

    public function __construct($changes)
    {
        $this->changes = $changes;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
                    ->subject('تنبيه: تم تحديث بيانات متجرك')
                    ->line('مرحباً ' . $notifiable->name . ',')
                    ->line('نود إعلامك بأنه تم تحديث البيانات التالية الخاصة بمتجرك ('. $this->changes['store_name'] .') بواسطة السوبر أدمن:')
                    ->line('---');

        // إضافة التغييرات التي حدثت فقط
        if(isset($this->changes['owner_name'])) {
            $mail->line('**اسم صاحب المتجر:** تغير من "' . $this->changes['owner_name']['old'] . '" إلى "' . $this->changes['owner_name']['new'] . '"');
        }
        if(isset($this->changes['owner_email'])) {
            $mail->line('**إيميل صاحب المتجر:** تغير من "' . $this->changes['owner_email']['old'] . '" إلى "' . $this->changes['owner_email']['new'] . '"');
        }
        if(isset($this->changes['subdomain'])) {
            $mail->line('**الدومين الفرعي:** تغير من "' . $this->changes['subdomain']['old'] . '" إلى "' . $this->changes['subdomain']['new'] . '"');
        }

        $mail->line('---')
             ->line('إذا لم تكن على علم بهذا التغيير، يرجى التواصل مع الإدارة.');

        return $mail;
    }
}
