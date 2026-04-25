<?php

namespace App\Filament\Resources\Readings\Pages;

use App\Filament\Resources\Readings\ReadingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReadings extends ListRecords
{
    protected static string $resource = ReadingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
