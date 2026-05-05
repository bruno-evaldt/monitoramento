<?php

namespace App\Mail;

use App\Models\Apartment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeakWarningMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $apartment;
    public $minutes;
    public $isFinalWarning;

    /**
     * Create a new message instance.
     */
    public function __construct(Apartment $apartment, int $minutes, bool $isFinalWarning = false)
    {
        $this->apartment = $apartment;
        $this->minutes = $minutes;
        $this->isFinalWarning = $isFinalWarning;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isFinalWarning 
            ? 'ALERTA FINAL: Consumo contínuo de água detectado' 
            : 'Aviso: Possível vazamento de água detectado';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.leak_warning',
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
