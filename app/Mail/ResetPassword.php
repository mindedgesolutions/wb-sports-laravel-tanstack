<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use function Pest\Laravel\from;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $fromEmail, $fromName, $body, $link, $subject;

    /**
     * Create a new message instance.
     */
    public function __construct($fromEmail, $fromName, $body, $link, $subject)
    {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->body = $body;
        $this->link = $link;
        $this->subject = $subject;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
            from: new Address($this->fromEmail, $this->fromName),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'resetemail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
