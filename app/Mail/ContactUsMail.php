<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactUsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contactData;

    public function __construct($contactData)
    {
        $this->contactData = $contactData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Contact Inquiry: ' . $this->contactData['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-us',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
