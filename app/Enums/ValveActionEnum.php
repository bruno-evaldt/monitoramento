<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ValveActionEnum: string implements HasLabel, HasColor, HasIcon
{
    case OPENED = 'opened';
    case CLOSED = 'closed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::OPENED => 'Aberta (Água Liberada)',
            self::CLOSED => 'Fechada (Água Trancada)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::OPENED => 'success',
            self::CLOSED => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::OPENED => 'heroicon-o-lock-open',
            self::CLOSED => 'heroicon-o-lock-closed',
        };
    }
}
