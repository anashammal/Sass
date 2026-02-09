<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;
    public $messageBody;
    public $subjectText; // Changed from $subject to avoid conflict with $this->subject()
    public $attachmentPath;
    public $filename;

    public function __construct($subjectText, $messageBody, $attachmentPath = null, $filename = 'report.pdf') {
        $this->subjectText = $subjectText;
        $this->messageBody = $messageBody;
        $this->attachmentPath = $attachmentPath;
        $this->filename = $filename;
    }

    public function build() {
        $email = $this->subject($this->subjectText)
                      ->from(config('mail.from.address'), config('mail.from.name'))
                      ->replyTo(config('mail.from.address'), config('mail.from.name'))
                      ->view('emails.generic_report');
        
        if ($this->attachmentPath && file_exists($this->attachmentPath)) {
            $email->attach($this->attachmentPath, [
                'as' => $this->filename,
                'mime' => 'application/pdf',
            ]);
        }
        
        $email->withSwiftMessage(function ($message) {
            $domain = 'tech-sys.online';
            // Restoring headers but focusing on clean Message-ID
            $messageId = md5(uniqid()) . '@' . $domain;
            $message->getHeaders()->addTextHeader('Message-ID', '<' . $messageId . '>');
            $message->getHeaders()->addTextHeader('X-Mailer', 'PHP/' . phpversion());
        });

        return $email;
    }
}
