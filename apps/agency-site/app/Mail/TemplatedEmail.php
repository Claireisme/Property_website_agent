<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class TemplatedEmail extends Mailable
{
    public function __construct(
        public string $subjectLine,
        public string $htmlBody,
        public string $textBody,
        public ?string $replyToEmail = null,
    ) {}

    public function build(): self
    {
        $mail = $this
            ->subject($this->subjectLine)
            ->html($this->htmlBody)
            ->text('emails.plain', ['body' => $this->textBody]);

        if (filled($this->replyToEmail)) {
            $mail->replyTo($this->replyToEmail);
        }

        return $mail;
    }
}
