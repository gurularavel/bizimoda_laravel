<?php

namespace App\Mail;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public FormSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Saytdan yeni müraciət'.($this->submission->subject ? ': '.$this->submission->subject : ''),
            replyTo: $this->submission->email ? [new Address($this->submission->email, (string) $this->submission->name)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact');
    }
}
