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
        return $this->subject(__('email_verification_subject', ['app_name' => config('app.name')]))
                    ->html("<h3>" . __('email_verification_header') . "</h3><h1 style='color:#10b981;'>{$this->code}</h1><p>" . __('email_verification_instruction') . "</p> <p style='font-size:12px; color:#666;'>TechSys - " . date('Y') . "</p>");
    }
}
