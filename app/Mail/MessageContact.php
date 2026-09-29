<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Message envoyé depuis le formulaire de contact.
 */
class MessageContact extends Mailable
{
    public function __construct(
        public string $nom,
        public string $email,
        public string $sujet,
        public string $contenu,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->email, $this->nom)],
            subject: "Contact · {$this->sujet} · {$this->nom}",
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.contact');
    }
}
