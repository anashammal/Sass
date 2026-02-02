<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('رمز التحقق - ' . config('app.name'))
                    ->html("<h3>رمز التحقق الخاص بك هو:</h3><h1 style='color:#10b981;'>{$this->code}</h1><p>يرجى إدخال هذا الرمز في المتصفح لإتمام عملية التحقق.</p>");
    }
}
