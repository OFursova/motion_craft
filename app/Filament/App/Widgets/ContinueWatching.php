<?php

namespace App\Filament\App\Widgets;

use Filament\Widgets\Widget;

class ContinueWatching extends Widget
{
    protected static ?int $sort = -2;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.app.widgets.continue-watching';
}
