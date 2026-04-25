<?php

namespace App\Filament\Resources\Readings;

use App\Filament\Resources\Readings\Pages\CreateReading;
use App\Filament\Resources\Readings\Pages\EditReading;
use App\Filament\Resources\Readings\Pages\ListReadings;
use App\Filament\Resources\Readings\Schemas\ReadingForm;
use App\Filament\Resources\Readings\Tables\ReadingsTable;
use App\Models\Reading;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReadingResource extends Resource
{
    protected static ?string $model = Reading::class;

    protected static ?string $modelLabel = 'Leitura';
    protected static ?string $pluralModelLabel = 'Leituras';

    public static function getNavigationGroup(): ?string
    {
        return 'Monitoramento';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    public static function form(Schema $schema): Schema
    {
        return ReadingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReadingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && $user->hasRole(\App\Enums\RoleEnum::MORADOR->value) && !$user->hasRole(\App\Enums\RoleEnum::SUPER_ADMIN->value)) {
            return $query->whereHas('apartment', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReadings::route('/'),
            'create' => CreateReading::route('/create'),
            'edit' => EditReading::route('/{record}/edit'),
        ];
    }
}
