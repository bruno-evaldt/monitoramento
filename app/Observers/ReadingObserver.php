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
        // Pega as últimas 5 leituras do apartamento ordenadas por ID
        $lastReadings = Reading::where('apartment_id', $apartment->id)
            ->latest('id')
            ->take(5)
            ->get();

        $consecutiveActive = 0;
        foreach ($lastReadings as $r) {
            if ($r->volume > 0) {
                $consecutiveActive++;
            } else {
                break;
            }
        }

        if ($consecutiveActive === 2) {
            // 2 minutos ininterruptos de consumo (2 leituras seguidas com volume > 0)
            $this->sendLeakNotification($apartment, false);
        } elseif ($consecutiveActive === 3) {
            // 3 minutos ininterruptos de consumo (3 leituras seguidas com volume > 0) - Último alerta
            $this->sendLeakNotification($apartment, true);
        }
    }

    private function sendLeakNotification(Apartment $apartment, bool $isLastAlert)
    {
        if ($apartment->user) {
            $apartment->user->notify(new LeakDetectedNotification($apartment, $isLastAlert));
        }

        $admins = User::role('super_admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new LeakDetectedNotification($apartment, $isLastAlert));
        }
    }
}
