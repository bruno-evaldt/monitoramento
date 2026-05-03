<?php

namespace App\Filament\Resources\Apartments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ApartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Morador')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('number')
                    ->label('Número do Apto')
                    ->searchable(),
                TextColumn::make('block')
                    ->label('Bloco')
                    ->searchable(),
                \Filament\Tables\Columns\IconColumn::make('valve_status')
                    ->boolean()
                    ->alignCenter()
                    ->label('Status da Válvula'),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('daily_limit_volume')
                    ->label('Limite Diário (L)')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\Action::make('toggleValve')
                    ->label(fn($record) => $record->valve_status ? 'Aberto' : 'Fechado')
                    ->icon(fn($record) => $record->valve_status ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->color(fn($record) => $record->valve_status ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn($record) => $record->valve_status ? 'Trancar fornecimento de água?' : 'Liberar fornecimento de água?')
                    ->action(function ($record) {
                        $newState = !$record->valve_status;
                        $record->update(['valve_status' => $newState]);

                        // Publicar via MQTT se tiver Device cadastrado
                        if ($record->device && $record->device->mac_address) {
                            // Envia também o relay_pin no tópico para a placa saber QUAL relé acionar
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

                        // Registra o Log do Síndico/Morador que apertou o botão
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
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\Action::make('requestReading')
                        ->label('Solicitar Leitura')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
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
                    \Filament\Actions\ViewAction::make(),
                    \Filament\Actions\EditAction::make(),
                    \Filament\Actions\DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
