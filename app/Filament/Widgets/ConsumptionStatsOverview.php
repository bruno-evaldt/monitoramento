<?php

namespace App\Filament\Widgets;

use App\Models\Reading;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ConsumptionStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $query = Reading::query();

        // Filtra pelo morador para exibir apenas o seu próprio consumo
        if ($user && $user->hasRole(\App\Enums\RoleEnum::MORADOR->value) && !$user->hasRole([\App\Enums\RoleEnum::SUPER_ADMIN->value, \App\Enums\RoleEnum::SINDICO->value])) {
            $apartmentIds = $user->apartments()->pluck('id');
            $query->whereIn('apartment_id', $apartmentIds);
        }

        // 1. Consumo Hoje
        $queryToday = clone $query;
        $totalToday = $queryToday->whereDate('read_at', Carbon::today())->sum('volume');
        $queryYesterday = clone $query;
        $totalYesterday = $queryYesterday->whereDate('read_at', Carbon::yesterday())->sum('volume');
        
        $diffToday = $totalToday - $totalYesterday;
        $descToday = abs($diffToday) . ' L ' . ($diffToday >= 0 ? 'a mais' : 'a menos') . ' que ontem';
        $iconToday = $diffToday >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $colorToday = $diffToday > 0 ? 'danger' : 'success';

        // 2. Consumo Este Mês
        $queryThisMonth = clone $query;
        $totalThisMonth = $queryThisMonth->whereMonth('read_at', Carbon::now()->month)->whereYear('read_at', Carbon::now()->year)->sum('volume');
        $queryLastMonth = clone $query;
        $totalLastMonth = $queryLastMonth->whereMonth('read_at', Carbon::now()->subMonth()->month)->whereYear('read_at', Carbon::now()->subMonth()->year)->sum('volume');

        $diffMonth = $totalThisMonth - $totalLastMonth;
        $descMonth = abs($diffMonth) . ' L ' . ($diffMonth >= 0 ? 'a mais' : 'a menos') . ' que o mês passado';
        $iconMonth = $diffMonth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $colorMonth = $diffMonth > 0 ? 'danger' : 'success';

        // Gráfico últimos 7 dias
        $chartQuery = clone $query;
        $readings7Days = $chartQuery->select(DB::raw('DATE(read_at) as date'), DB::raw('SUM(volume) as total'))
            ->where('read_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        $chartData = $readings7Days->pluck('total')->toArray();
        if (empty($chartData)) {
            $chartData = [0, 0];
        }

        return [
            Stat::make('Consumo Hoje (' . Carbon::today()->format('d/m') . ')', number_format($totalToday, 2, ',', '.') . ' L')
                ->description($descToday)
                ->descriptionIcon($iconToday)
                ->color($colorToday),

            Stat::make('Últimos 7 Dias', number_format(array_sum($chartData), 2, ',', '.') . ' L')
                ->description('De ' . Carbon::now()->subDays(7)->format('d/m') . ' até hoje')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->chart($chartData)
                ->color('primary'),

            Stat::make('Consumo Este Mês', number_format($totalThisMonth, 2, ',', '.') . ' L')
                ->description($descMonth)
                ->descriptionIcon($iconMonth)
                ->chart($chartData)
                ->color($colorMonth),
        ];
    }
}
