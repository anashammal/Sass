<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BrandEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.brand') // اسم ملف التصميم الذي أنشأته
                    ->subject('إشعار رسمي من TechSys') // عنوان الرسالة الذي سيظهر للمستقبل
                    ->from('noreply@tech-sys.online', 'TechSys System'); // تحديد اسم المرسل بوضوح
    }
}