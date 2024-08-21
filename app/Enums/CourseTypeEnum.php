<?php

namespace App\Enums;

use App\Models\Course;
use Filament\Support\Colors\Color;

enum CourseTypeEnum: string
{
    case Available = 'available';
    case Favorite = 'favorite';
    case Finished = 'finished';
    case Watchlist = 'watchlist';

    public static function getStatus(Course $record, string $type): string|array|null
    {
        if ($record->users->first()?->pivot->completed_at) {
            return match ($type) {
                'tooltip' => 'courses.tooltip.finished',
                'color' => Color::Emerald,
                'icon' => 'heroicon-c-check-circle',
            };
        }

        if ($record->users->first()?->pivot->favorite) {
            return match ($type) {
                'tooltip' => 'courses.tooltip.favorite',
                'color' => Color::Red,
                'icon' => 'heroicon-s-heart',
            };
        }

        if ($record->users->first()?->pivot->purchased_at) {
            return match ($type) {
                'tooltip' => 'courses.tooltip.available',
                'color' => Color::Amber,
                'icon' => 'heroicon-c-check-circle',
            };
        }

        if ($record->users->first()?->pivot->watchlist) {
            return match ($type) {
                'tooltip' => 'courses.tooltip.watchlist',
                'color' => Color::Purple,
                'icon' => 'heroicon-o-bars-3-center-left',
            };
        }

        return match ($type) {
            'tooltip' => 'courses.tooltip.default',
            'color' => Color::Gray,
            'icon' => 'heroicon-o-minus',
        };
    }
}
