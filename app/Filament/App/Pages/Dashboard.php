<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected ?string $heading = '';

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }
}
