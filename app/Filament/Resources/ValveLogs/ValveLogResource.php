<?php

namespace App\Filament\Resources\ValveLogs;

use App\Filament\Resources\ValveLogs\Pages\CreateValveLog;
use App\Filament\Resources\ValveLogs\Pages\EditValveLog;
use App\Filament\Resources\ValveLogs\Pages\ListValveLogs;
use App\Filament\Resources\ValveLogs\Schemas\ValveLogForm;
use App\Filament\Resources\ValveLogs\Tables\ValveLogsTable;
use App\Models\ValveLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ValveLogResource extends Resource
{
    protected static ?string $model = ValveLog::class;

    protected static ?string $modelLabel = 'Log da Válvula';
    protected static ?string $pluralModelLabel = 'Logs das Válvulas';

    public static function getNavigationGroup(): ?string
    {
        return 'Monitoramento';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    public static function form(Schema $schema): Schema
    {
        return ValveLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ValveLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListValveLogs::route('/'),
            'create' => CreateValveLog::route('/create'),
            'edit' => EditValveLog::route('/{record}/edit'),
        ];
    }
}
