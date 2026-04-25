<?php

namespace App\Filament\Resources\ValveLogs\Pages;

use App\Filament\Resources\ValveLogs\ValveLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListValveLogs extends ListRecords
{
    protected static string $resource = ValveLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
