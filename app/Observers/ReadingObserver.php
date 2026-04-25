<?php

namespace App\Observers;

use App\Models\Reading;
use App\Models\Apartment;
use App\Models\User;
use App\Notifications\LeakDetectedNotification;
use App\Notifications\ExcessiveConsumptionNotification;
use Illuminate\Support\Facades\Cache;

class ReadingObserver
{
    public function created(Reading $reading): void
    {
        $apartment = $reading->apartment;
        if (!$apartment) return;

        $this->checkExcessiveConsumption($apartment, $reading);
        $this->checkContinuousLeak($apartment, $reading);
    }

    private function checkExcessiveConsumption(Apartment $apartment, Reading $reading)
    {
        if (!$apartment->daily_limit_volume) {
            return; // Sem limite definido
        }

        // Calcula o consumo de hoje
        $todayVolume = Reading::where('apartment_id', $apartment->id)
            ->whereDate('created_at', today())
            ->sum('volume');

        if ($todayVolume >= $apartment->daily_limit_volume) {
            // Evitar spam: Envia notificação apenas 1x por dia por apartamento
            $cacheKey = "notified_excessive_{$apartment->id}_" . today()->format('Ymd');
            if (!Cache::has($cacheKey)) {
                
                if ($apartment->user) {
                    $apartment->user->notify(new ExcessiveConsumptionNotification($apartment, $todayVolume));
                }

                // Avisa os admins
                $admins = User::role('super_admin')->get(); // Shield default role is usually super_admin or admin
                foreach($admins as $admin) {
                    $admin->notify(new ExcessiveConsumptionNotification($apartment, $todayVolume));
                }

                Cache::put($cacheKey, true, now()->endOfDay());
            }
        }
    }

    private function checkContinuousLeak(Apartment $apartment, Reading $reading)
    {
        // Se a placa envia 1 leitura por minuto (quando há fluxo),
        // 15 leituras seguidas com volume > 0 em um intervalo de ~15 minutos indicam vazamento.

        // Pega as últimas 15 leituras
        $lastReadings = Reading::where('apartment_id', $apartment->id)
            ->latest()
            ->take(15)
            ->get();

        if ($lastReadings->count() < 15) return;

        // Verifica se todas as 15 leituras têm volume > 0 (fluxo contínuo) e estão em um intervalo curto de tempo (ex: max 20 minutos entre a primeira e a última dessas 15)
        $isContinuous = $lastReadings->every(function ($r) {
            return $r->volume > 0;
        });

        $firstOfFifteen = $lastReadings->last();
        $lastOfFifteen = $lastReadings->first();
        
        $timeDiffMinutes = $lastOfFifteen->created_at->diffInMinutes($firstOfFifteen->created_at);

        // Se foram 15 leituras ininterruptas em menos de 20 minutos
        if ($isContinuous && $timeDiffMinutes <= 20) {
            
            $cacheKey = "notified_leak_{$apartment->id}_" . now()->format('YmdH'); // 1 alerta por hora
            if (!Cache::has($cacheKey)) {

                if ($apartment->user) {
                    $apartment->user->notify(new LeakDetectedNotification($apartment));
                }

                $admins = User::role('super_admin')->get();
                foreach($admins as $admin) {
                    $admin->notify(new LeakDetectedNotification($apartment));
                }

                Cache::put($cacheKey, true, now()->addHour());
            }
        }
    }
}
