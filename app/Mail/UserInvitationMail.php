<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $signedUrl)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vous avez été invité à rejoindre notre application !',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.user-invitation',
            with: [
                'url' => $this->signedUrl,
            ],
        );
    }
}