<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ReadingTypeEnum: string implements HasLabel, HasColor, HasIcon
{
    case AUTOMATIC = 'automatic';
    case MANUAL = 'manual';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AUTOMATIC => 'Automática',
            self::MANUAL => 'Manual (Solicitada)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::AUTOMATIC => 'gray',
            self::MANUAL => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::AUTOMATIC => 'heroicon-o-cpu-chip',
            self::MANUAL => 'heroicon-o-user',
        };
    }
}
