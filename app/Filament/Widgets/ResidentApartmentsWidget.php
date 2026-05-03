<?php

namespace App\Filament\Widgets;

use App\Models\Apartment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;

class ResidentApartmentsWidget extends BaseWidget
{
    protected static ?string $heading = '';
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole(\App\Enums\RoleEnum::MORADOR->value) && !$user->hasRole([\App\Enums\RoleEnum::SUPER_ADMIN->value, \App\Enums\RoleEnum::SINDICO->value]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Apartment::query()->where('user_id', auth()->id())
            )
            ->columns([
                TextColumn::make('valve_status')
                    ->label('')
                    ->formatStateUsing(fn($state) => $state ? 'Aberto' : 'Trancado')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),
            ])
            ->actions([
                Action::make('toggleValve')
                    ->label(fn($record) => $record->valve_status ? 'Trancar' : 'Liberar')
                    ->icon(fn($record) => $record->valve_status ? 'heroicon-m-lock-closed' : 'heroicon-m-lock-open')
                    ->color(fn($record) => $record->valve_status ? 'danger' : 'success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading(fn($record) => $record->valve_status ? 'Trancar fornecimento de água?' : 'Liberar fornecimento de água?')
                    ->action(function ($record) {
                        $newState = !$record->valve_status;
                        $record->update(['valve_status' => $newState]);

                        if ($record->device && $record->device->mac_address) {
                            $topic = "condominio/valves/" . $record->device->mac_address . "/" . $record->device->relay_pin;
                            $payload = json_encode(['status' => $newState]);

                            try {
                                $server = env('MQTT_HOST', 'broker.emqx.io');
                                $port = env('MQTT_PORT', 1883);
                                $clientId = env('MQTT_CLIENT_ID', 'laravel_pub') . '_' . rand(1, 999);
                                $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
                                $mqtt->connect();
                                $mqtt->publish($topic, $payload, 0);
                                $mqtt->disconnect();
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Erro ao comunicar com a Placa MQTT')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }

                        \App\Models\ValveLog::create([
                            'apartment_id' => $record->id,
                            'user_id' => auth()->id(),
                            'action' => $newState ? \App\Enums\ValveActionEnum::OPENED : \App\Enums\ValveActionEnum::CLOSED,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title($newState ? 'Água Liberada!' : 'Água Trancada!')
                            ->success()
                            ->send();
                    }),

                Action::make('requestReading')
                    ->label('Leitura')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->button()
                    ->action(function ($record) {
                        if ($record->device && $record->device->mac_address && $record->device->sensor_pin) {
                            $topic = "condominio/commands/" . $record->device->mac_address . "/" . $record->device->sensor_pin;
                            $payload = json_encode(['action' => 'read']);

                            try {
                                $server = env('MQTT_HOST', 'broker.emqx.io');
                                $port = env('MQTT_PORT', 1883);
                                $clientId = env('MQTT_CLIENT_ID', 'laravel_pub') . '_req_' . rand(1, 999);
                                $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
                                $mqtt->connect();
                                $mqtt->publish($topic, $payload, 0);
                                $mqtt->disconnect();

                                \Filament\Notifications\Notification::make()
                                    ->title('Leitura Solicitada')
                                    ->body('A placa enviará os dados atualizados em instantes.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Erro ao contatar Placa')
                                    ->danger()
                                    ->send();
                            }
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Apto sem Dispositivo vinculado')
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->paginated(false);
    }
}
