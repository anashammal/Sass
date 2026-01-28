<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    // سنقوم بتخزين التوكن (الرمز) الذي أرسله لارافيل
    public $token;

    /**
     * إنشاء إشعار جديد.
     */
    public function __construct($token) // <-- لارافيل سيمرر التوكن هنا
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        // 1. إنشاء الرابط الذي سيضغط عليه المستخدم
        $resetUrl = url(route('password.reset', [
            'token' => $this->token, // <-- استخدام التوكن الخاص بنا
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // 2. بناء رسالة الإيميل المترجمة
        return (new MailMessage)
                    ->subject('إشعار إعادة تعيين كلمة المرور (TechSys)')
                    ->line('لقد تلقيت هذا البريد الإلكتروني لأننا تلقينا طلب إعادة تعيين كلمة مرور لحسابك.')
                    ->action('إعادة تعيين كلمة المرور', $resetUrl)
                    ->line('هذا الرابط صالح لمدة 60 دقيقة فقط.')
                    ->line('إذا لم تقم بطلب إعادة تعيين كلمة المرور، فلا داعي لاتخاذ أي إجراء آخر.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
