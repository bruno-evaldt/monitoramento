<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\Device;
use App\Models\Reading;

class MqttListenerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fica ouvindo o Broker MQTT aguardando os consumos de água (readings)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $server   = env('MQTT_HOST', 'broker.emqx.io');
        $port     = env('MQTT_PORT', 1883);
        $clientId = env('MQTT_CLIENT_ID', 'laravel_listener_' . rand(1000, 9999));

        $this->info("Conectando ao Broker MQTT ({$server}:{$port})...");

        try {
            $mqtt = new MqttClient($server, $port, $clientId);

            $connectionSettings = (new ConnectionSettings)
                ->setKeepAliveInterval(60)
                ->setUseTls(false); // Broker público geralmente não exige TLS na porta 1883

            $mqtt->connect($connectionSettings, true);

            $this->info("Conectado com Sucesso! Aguardando leituras...");

            // Tópico padrão onde a placa vai enviar: "condominio/readings/{mac}/{sensor_pin}"
            // O sinal # (hash) funciona como curinga para tudo o que vier depois
            $mqtt->subscribe('condominio/readings/#', function (string $topic, string $message) {
                // $topic será ex: condominio/readings/A1:B2:C3:D4:E5:F6/36
                $topicParts = explode('/', $topic);
                $sensorPin = end($topicParts);
                $macAddress = prev($topicParts);

                $this->line("Recebido do MAC [{$macAddress}] Sensor [{$sensorPin}]: {$message}");

                $data = json_decode($message, true);

                if (isset($data['volume'])) {
                    // Encontrar a qual apartamento/device esse pino de sensor pertence
                    $device = Device::where('mac_address', $macAddress)
                                    ->where('sensor_pin', $sensorPin)
                                    ->first();

                        if ($device) {
                            $isManual = isset($data['type']) && $data['type'] === 'manual';
                            
                            // Salva o histórico (Reading) associado ao Apartamento desse Device
                            Reading::create([
                                'apartment_id' => $device->apartment_id,
                                'volume' => $data['volume'],
                                'reading_type' => $isManual ? \App\Enums\ReadingTypeEnum::MANUAL : \App\Enums\ReadingTypeEnum::AUTOMATIC,
                                'read_at' => now(),
                            ]);
                            $this->info("✔ Leitura (" . ($isManual ? 'Manual' : 'Automática') . ") salva com sucesso pro Ap: " . $device->apartment_id);

                            // Lógica de verificação de vazamento para leituras regulares (automáticas)
                            if (!$isManual) {
                                $apartmentId = $device->apartment_id;
                                $cacheKeyStart = "flow_start_{$apartmentId}";
                                $cacheKeyLast = "flow_last_{$apartmentId}";
                                $cacheKeyLevel = "flow_level_{$apartmentId}";

                                $volume = (float) $data['volume'];
                                $now = now()->timestamp;

                                if ($volume > 0) {
                                    $lastReadingTimeStr = \Illuminate\Support\Facades\Cache::get($cacheKeyLast);

                                    // Se não tem leitura anterior ou a última foi há mais de 2 minutos (fluxo parou e voltou)
                                    if (!$lastReadingTimeStr || ($now - (int)$lastReadingTimeStr) > 120) {
                                        \Illuminate\Support\Facades\Cache::put($cacheKeyStart, $now);
                                        \Illuminate\Support\Facades\Cache::put($cacheKeyLast, $now);
                                        \Illuminate\Support\Facades\Cache::put($cacheKeyLevel, 0);
                                    } else {
                                        // Fluxo contínuo
                                        \Illuminate\Support\Facades\Cache::put($cacheKeyLast, $now);
                                        
                                        $startTime = (int)\Illuminate\Support\Facades\Cache::get($cacheKeyStart);
                                        $level = (int)\Illuminate\Support\Facades\Cache::get($cacheKeyLevel, 0);
                                        
                                        $durationMinutes = floor(($now - $startTime) / 60);

                                        $apartment = \App\Models\Apartment::with('user')->find($apartmentId);

                                        if ($apartment && $apartment->user && $apartment->user->email) {
                                            if ($durationMinutes >= 3 && $level == 0) {
                                                \Illuminate\Support\Facades\Mail::to($apartment->user)->send(new \App\Mail\LeakWarningMail($apartment, 3, false));
                                                \Illuminate\Support\Facades\Cache::put($cacheKeyLevel, 1);
                                                $this->warn("Aviso 1 enviado para Ap {$apartmentId} (3 min de fluxo)");
                                            } elseif ($durationMinutes >= 8 && $level == 1) {
                                                \Illuminate\Support\Facades\Mail::to($apartment->user)->send(new \App\Mail\LeakWarningMail($apartment, 8, true));
                                                \Illuminate\Support\Facades\Cache::put($cacheKeyLevel, 2);
                                                $this->warn("Aviso 2 enviado para Ap {$apartmentId} (8 min de fluxo)");
                                            }
                                        }
                                    }
                                } else {
                                    // Volume 0 = fluxo parou
                                    \Illuminate\Support\Facades\Cache::forget($cacheKeyStart);
                                    \Illuminate\Support\Facades\Cache::forget($cacheKeyLast);
                                    \Illuminate\Support\Facades\Cache::forget($cacheKeyLevel);
                                }
                            }

                        } else {
                        $this->error("Placa MAC {$macAddress} com Sensor Pin {$sensorPin} não encontrada no banco (devices)!");
                    }
                }
            }, 0);

            // Inicia o Loop infinito ouvindo
            $mqtt->loop(true);

            $mqtt->disconnect();

        } catch (\Exception $e) {
            $this->error('Erro de Conexão: ' . $e->getMessage());
        }
    }
}
