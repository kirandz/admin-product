<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AccountWidget extends Widget
{
    protected int | string | array $columnSpan = 'full';
    protected static string $view = 'filament-panels::widgets.account-widget';
}
