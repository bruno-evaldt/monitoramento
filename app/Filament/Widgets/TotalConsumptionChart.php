<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class TotalConsumptionChart extends ChartWidget
{
    protected ?string $heading = 'Total Consumption Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
