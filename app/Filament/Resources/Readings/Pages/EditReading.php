<?php

namespace App\Filament\Resources\Readings\Pages;

use App\Filament\Resources\Readings\ReadingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReading extends EditRecord
{
    protected static string $resource = ReadingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
