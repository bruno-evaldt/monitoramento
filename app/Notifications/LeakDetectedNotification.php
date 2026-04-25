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

    public function __construct(Apartment $apartment)
    {
        $this->apartment = $apartment;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->error()
                    ->subject('ALERTA: Possível Vazamento Detectado')
                    ->greeting('Olá!')
                    ->line("O sistema detectou um fluxo contínuo de água no apartamento {$this->apartment->number}.")
                    ->line('Por favor, verifique se há alguma torneira aberta ou cano rompido.')
                    ->action('Ver Painel', url('/app'))
                    ->line('Se não for você, considere trancar a válvula pelo sistema.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'apartment_id' => $this->apartment->id,
            'message' => "Possível vazamento detectado no apartamento {$this->apartment->number}.",
            'type' => 'leak'
        ];
    }
}
