<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password; // <-- !! مهم جدًا !!

class StoreOwnerWelcomeNotification extends Notification
{
    use Queueable;

    /**
     * إنشاء إشعار جديد.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        // 1. إنشاء توكن (Token) آمن لمرة واحدة
        $token = Password::broker()->createToken($notifiable);

        // 2. إنشاء الرابط الذي سيضغط عليه المستخدم
        // (سيستخدم صفحة "إعادة تعيين كلمة المرور" الافتراضية)
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // 3. بناء رسالة الإيميل
        return (new MailMessage)
                    ->subject('أهلاً بك! قم بتعيين كلمة المرور الخاصة بك')
                    ->line('أهلاً بك في نظامنا. لقد تم إنشاء حسابك بنجاح.')
                    ->line('نرجو منك الضغط على الزر أدناه لتعيين كلمة المرور الخاصة بك وبدء استخدام حسابك.')
                    ->action('تعيين كلمة المرور', $resetUrl)
                    ->line('هذا الرابط صالح لمدة 60 دقيقة فقط.')
                    ->line('إذا لم تقم بطلب هذا الحساب، نرجو تجاهل هذه الرسالة.');
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
