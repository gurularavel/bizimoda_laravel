<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TestMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'bizimoda — test e-poçtu');
    }

    public function content(): Content
    {
        return new Content(htmlString: '<p>Bu, admin paneldən göndərilmiş test məktubudur. SMTP parametrləri düzgün işləyir.</p><p>'.now()->format('d.m.Y H:i').'</p>');
    }
}
