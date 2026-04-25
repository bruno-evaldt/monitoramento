<?php

namespace App\Filament\Resources\Apartments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ApartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('user_id')
                    ->label('Morador')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('number')
                    ->label('Número do Apto')
                    ->required(),
                TextInput::make('block')
                    ->label('Bloco'),
                Toggle::make('valve_status')
                    ->label('Status da Válvula')
                    ->required(),
                TextInput::make('daily_limit_volume')
                    ->label('Limite Diário (Litros)')
                    ->numeric(),
            ]);
    }
}
