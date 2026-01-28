<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactWelcomeNotification extends Notification
{
    use Queueable;

    protected $contact;

    public function __construct($contact)
    {
        $this->contact = $contact;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $type = $this->contact->is_supplier ? 'مورد' : 'زبون';
        $subject = 'مرحباً بك في نظام ' . config('app.name');

        return (new MailMessage)
                    ->subject($subject)
                    ->line('مرحباً ' . $this->contact->contact_name . '،')
                    ->line('تم تسجيلك كـ **' . $type . '** لدينا في نظام ' . config('app.name') . ' بنجاح.')
                    ->line('رقم هاتفك المسجل هو: ' . $this->contact->phone)
                    ->line('**رصيدك الحالي (حد الدين):** ' . number_format($this->contact->credit_limit, 2) . ' ليرة.')
                    ->line('نحن نتطلع للعمل معك. شكراً لثقتك.');
    }
}
