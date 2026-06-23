<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Apartment;

class LeakDetectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $apartment;
    protected $isLastAlert;

    public function __construct(Apartment $apartment, bool $isLastAlert = false)
    {
        $this->apartment = $apartment;
        $this->isLastAlert = $isLastAlert;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->isLastAlert ? 'ALERTA FINAL: Vazamento Contínuo de Água!' : 'ALERTA: Possível Vazamento Detectado';

        return (new MailMessage)
                    ->subject($subject)
                    ->view('emails.leak_warning', [
                        'apartment' => $this->apartment,
                        'isFinalWarning' => $this->isLastAlert,
                    ]);
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->isLastAlert 
            ? "ALERTA FINAL: Vazamento contínuo persistente no apartamento {$this->apartment->number}." 
            : "Possível vazamento detectado no apartamento {$this->apartment->number}.";

        return [
            'apartment_id' => $this->apartment->id,
            'message' => $message,
            'type' => 'leak'
        ];
    }
}
