<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SportsFeedback extends Mailable
{
    use Queueable, SerializesModels;

    public $feedbackType, $fromName, $fromMobile, $fromEmail, $fromAddress, $subject, $userMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($feedbackType, $fromName, $fromMobile, $fromEmail, $fromAddress, $subject, $userMessage)
    {
        $this->feedbackType = $feedbackType;
        $this->fromName = $fromName;
        $this->fromMobile = $fromMobile;
        $this->fromEmail = $fromEmail;
        $this->fromAddress = $fromAddress;
        $this->subject = $subject;
        $this->userMessage = $userMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Feedback from website dated ' . date('d/m/Y H:i:s'),
            from: new Address($this->fromEmail, $this->fromName),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'sports.feedback',
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
