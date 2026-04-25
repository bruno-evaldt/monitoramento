<?php

namespace App\Filament\Resources\Devices\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DeviceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('apartment_id')
                    ->label('Apartamento')
                    ->relationship('apartment', 'number')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('mac_address')
                    ->label('Endereço MAC'),
                TextInput::make('relay_pin')
                    ->label('Pino do Relé (Válvula)')
                    ->required()
                    ->numeric(),
                TextInput::make('sensor_pin')
                    ->label('Pino do Sensor (Fluxo)')
                    ->required()
                    ->numeric(),
            ]);
    }
}
