<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class SalesOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $totalSalesToday = Sale::whereDate('date', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        $totalSalesThisMonth = Sale::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', 'completed')
            ->sum('total_amount');

        $totalSalesAllTime = Sale::where('status', 'completed')
            ->sum('total_amount');

        $salesCountToday = Sale::whereDate('date', $today)
            ->where('status', 'completed')
            ->count();

        $salesCountThisMonth = Sale::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', 'completed')
            ->count();

        return [
            Stat::make('Today Sales', 'Rp ' . number_format($totalSalesToday, 0, ',', '.'))
                ->description($salesCountToday . ' orders completed today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('This Month Sales', 'Rp ' . number_format($totalSalesThisMonth, 0, ',', '.'))
                ->description($salesCountThisMonth . ' orders completed this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([15, 30, 20, 45, 32, 56, 80])
                ->color('info'),

            Stat::make('All Time Sales', 'Rp ' . number_format($totalSalesAllTime, 0, ',', '.'))
                ->description('Total revenue from all completed sales')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),
        ];
    }
}

