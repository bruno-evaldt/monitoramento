<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\Reading;
use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Support\Facades\Log;

class MqttListenCommand extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Escuta os tópicos MQTT para receber dados de fluxo de água das placas Kincony.';

    public function handle()
    {
        $server   = env('MQTT_HOST', 'broker.emqx.io');
        $port     = env('MQTT_PORT', 1883);
        $clientId = env('MQTT_CLIENT_ID', 'laravel_backend_' . uniqid());
        $username = env('MQTT_USERNAME');
        $password = env('MQTT_PASSWORD');

        $connectionSettings = (new ConnectionSettings)
            ->setUsername($username)
            ->setPassword($password)
            ->setKeepAliveInterval(60);

        try {
            $mqtt = new MqttClient($server, $port, $clientId);
            $mqtt->connect($connectionSettings, true);
            
            $this->info("Conectado ao broker MQTT: $server:$port");

            // Tópico para todas as placas no formato: condominio/+/fluxo (+ é o ID do apartamento ou MAC)
            $topic = 'condominio/+/fluxo';

            $mqtt->subscribe($topic, function (string $topic, string $message) {
                // $topic = condominio/00:11:22:33:44:55/fluxo
                $parts = explode('/', $topic);
                $mac_address = $parts[1] ?? null;

                if ($mac_address) {
                    $this->processReading($mac_address, $message);
                }
            }, 0);

            $mqtt->loop(true);
            $mqtt->disconnect();
            
        } catch (\Exception $e) {
            $this->error("Erro no MQTT: " . $e->getMessage());
            Log::error("MQTT falhou", ['exception' => $e]);
        }
    }

    private function processReading($mac_address, $volume)
    {
        $this->info("Recebido $volume L do MAC $mac_address");

        $device = Device::where('mac_address', $mac_address)->first();

        if ($device && is_numeric($volume)) {
            Reading::create([
                'apartment_id' => $device->apartment_id,
                'volume' => $volume,
                'reading_type' => 'automatic',
                'read_at' => now(),
            ]);
            Log::info("Leitura de $volume registrada para o apartamento " . $device->apartment_id);
        } else {
            $this->warn("Device não encontrado para o MAC: $mac_address");
        }
    }
}
