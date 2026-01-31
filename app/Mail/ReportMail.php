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
                      ->from('noreply@tech-sys.online', 'TechSys')
                      ->replyTo('noreply@tech-sys.online', 'No Reply')
                      ->view('emails.generic_report');
        
        if ($this->attachmentPath && file_exists($this->attachmentPath)) {
            $email->attach($this->attachmentPath, [
                'as' => $this->filename,
                'mime' => 'application/pdf',
            ]);
        }
        
        return $email;
    }
}
