<?php

namespace App\Filament\Resources\ValveLogs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ValveLogForm
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
                \Filament\Forms\Components\Select::make('user_id')
                    ->label('Morador (Usuário)')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('action')
                    ->label('Ação (ABRIR/FECHAR)')
                    ->required(),
            ]);
    }
}
