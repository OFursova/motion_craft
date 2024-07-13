<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('Users'), User::count())
                ->description('Total users on the platform'),
            Stat::make(__('Courses'), Course::count())
                ->description('Total courses on the platform'),
            Stat::make(__('Lessons'), Lesson::count())
                ->description('Total lessons on the platform'),
        ];
    }
}
