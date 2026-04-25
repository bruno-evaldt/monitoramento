<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Support\Facades\Log;

class MqttService
{
    public function publishCommand($mac_address, $command)
    {
        $server   = env('MQTT_HOST', 'broker.emqx.io');
        $port     = env('MQTT_PORT', 1883);
        $clientId = env('MQTT_CLIENT_ID', 'laravel_backend_pub_' . uniqid());
        $username = env('MQTT_USERNAME');
        $password = env('MQTT_PASSWORD');

        $connectionSettings = (new ConnectionSettings)
            ->setUsername($username)
            ->setPassword($password);

        try {
            $mqtt = new MqttClient($server, $port, $clientId);
            $mqtt->connect($connectionSettings, true);

            $topic = "condominio/{$mac_address}/valvula/set";
            
            $mqtt->publish($topic, $command, 0);
            
            $mqtt->disconnect();
            
            Log::info("MQTT Publish: $command para $topic");
            return true;
        } catch (\Exception $e) {
            Log::error("Erro no MQTT Publish", ['exception' => $e]);
            return false;
        }
    }
}
