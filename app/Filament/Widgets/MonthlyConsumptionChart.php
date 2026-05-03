<?php

namespace App\Filament\Widgets;

use App\Models\Reading;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MonthlyConsumptionChart extends ChartWidget
{
    protected ?string $heading = 'Consumo Mensal';
    protected static ?int $sort = 2; // To appear below the StatsOverview (which usually has default sort)

    protected function getData(): array
    {
        $user = auth()->user();
        $query = Reading::query();

        // Filtra pelo morador para exibir apenas o seu próprio consumo
        if ($user && $user->hasRole(\App\Enums\RoleEnum::MORADOR->value) && !$user->hasRole([\App\Enums\RoleEnum::SUPER_ADMIN->value, \App\Enums\RoleEnum::SINDICO->value])) {
            $apartmentIds = $user->apartments()->pluck('id');
            $query->whereIn('apartment_id', $apartmentIds);
        }

        $data = [];
        $labels = [];

        // Buscar dados dos últimos 6 meses
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->startOfMonth()->subMonths($i);
            
            // Formatando como 'Mai/26'
            $labels[] = ucfirst($date->translatedFormat('M/y'));
            
            $queryMonth = clone $query;
            $total = $queryMonth->whereMonth('read_at', $date->month)
                                ->whereYear('read_at', $date->year)
                                ->sum('volume');
            
            $data[] = round($total, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Volume Consumido (Litros)',
                    'data' => $data,
                    'backgroundColor' => '#3b82f6', // Cor azul do Tailwind
                    'borderColor' => '#2563eb',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
