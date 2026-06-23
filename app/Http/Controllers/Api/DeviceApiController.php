<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Reading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceApiController extends Controller
{
    /**
     * Endpoint para receber leituras do dispositivo Kincony
     */
    public function storeReading(Request $request, $mac_address)
    {
        $request->validate([
            'volume' => 'required|numeric',
        ]);

        $device = Device::where('mac_address', $mac_address)->first();

        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        $cumulativeVolume = (float) $request->volume;
        $cacheKey = "device_last_cumulative_vol_{$device->id}";
        $lastCumulative = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if ($lastCumulative === null) {
            // Primeira leitura após inicialização do cache: define o baseline
            $delta = 0.0;
        } else {
            if ($cumulativeVolume >= $lastCumulative) {
                $delta = $cumulativeVolume - $lastCumulative;
            } else {
                // Dispositivo reiniciou, o volume cumulativo recomeça
                $delta = $cumulativeVolume;
            }
        }

        \Illuminate\Support\Facades\Cache::put($cacheKey, $cumulativeVolume);

        $reading = Reading::create([
            'apartment_id' => $device->apartment_id,
            'volume' => $delta,
            'reading_type' => 'automatic',
            'read_at' => now(),
        ]);

        Log::info("Reading stored for device {$mac_address}: {$delta}L (cumulative: {$cumulativeVolume}L)");

        return response()->json(['message' => 'Reading stored successfully', 'reading_id' => $reading->id], 201);
    }

    /**
     * Endpoint para a placa Kincony verificar se há comandos pendentes
     */
    public function getCommands(Request $request, $mac_address)
    {
        $device = Device::where('mac_address', $mac_address)->with('apartment')->first();

        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        // Retorna o status da válvula. A placa deve garantir que sua válvula local esteja igual a este status.
        return response()->json([
            'valve_status' => $device->apartment->valve_status ? 'OPEN' : 'CLOSED',
        ]);
    }
}
