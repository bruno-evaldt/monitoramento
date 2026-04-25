<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Apartment;

class ExcessiveConsumptionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $apartment;
    protected $volume;

    public function __construct(Apartment $apartment, $volume)
    {
        $this->apartment = $apartment;
        $this->volume = $volume;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->warning()
                    ->subject('Aviso: Consumo Excessivo Diário')
                    ->greeting('Olá!')
                    ->line("O consumo de água do apartamento {$this->apartment->number} ultrapassou o limite diário definido.")
                    ->line("Consumo registrado hoje: {$this->volume}L.")
                    ->action('Ver Painel', url('/app'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'apartment_id' => $this->apartment->id,
            'message' => "Consumo diário ultrapassou o limite. Total: {$this->volume}L.",
            'type' => 'excessive'
        ];
    }
}
