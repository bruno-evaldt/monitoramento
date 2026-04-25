<?php

namespace App\Filament\Resources\ValveLogs\Pages;

use App\Filament\Resources\ValveLogs\ValveLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditValveLog extends EditRecord
{
    protected static string $resource = ValveLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
