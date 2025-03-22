<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Sales';
    protected static ?string $pollingInterval = '60s';
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 'full';
    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => true,
            ],
        ],
    ];

    protected function getData(): array
    {
        $data = $this->getMonthlySalesData();

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Sales',
                    'data' => $data['monthlySales'],
                    'fill' => false,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.1,
                ],
            ],
            'labels' => $data['months'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getMonthlySalesData(): array
    {
        $now = Carbon::now();
        $months = collect(range(1, 12))->map(function ($month) use ($now) {
            return Carbon::createFromDate($now->year, $month, 1)->format('M');
        });

        $monthlySales = [];

        foreach (range(1, 12) as $month) {
            $startOfMonth = Carbon::createFromDate($now->year, $month, 1)->startOfMonth();
            $endOfMonth = Carbon::createFromDate($now->year, $month, 1)->endOfMonth();

            $totalSales = Sale::whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('status', 'completed')
                ->sum('total_amount');

            $monthlySales[] = $totalSales;
        }

        return [
            'months' => $months,
            'monthlySales' => $monthlySales,
        ];
    }
}
