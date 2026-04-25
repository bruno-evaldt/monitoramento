<?php

namespace App\Filament\Resources\Readings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReadingForm
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
                TextInput::make('volume')
                    ->label('Volume (Litros)')
                    ->required()
                    ->numeric(),
                TextInput::make('reading_type')
                    ->label('Tipo de Leitura')
                    ->required()
                    ->default('automatic'),
                DateTimePicker::make('read_at')
                    ->label('Lido em')
                    ->required(),
            ]);
    }
}
