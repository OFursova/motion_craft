<?php

namespace App\Filament\Entries;

use App\Enums\DurationEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Model;

final class CurriculumEntry
{
    public static function schema(?Model $course): array
    {
        return $course->units_count > 0
            ? self::unitsSchema($course)
            : self::itemsSchema($course);
    }

    public static function itemsSchema(?Model $course): array
    {
        $course->loadMissing(['lessons']);

        return [
            RepeatableEntry::make('lessons')
                ->hiddenLabel()
                ->schema([
                    TextEntry::make('type')
                        ->hiddenLabel()
                        ->badge()
                        ->formatStateUsing(fn($state) => __('courses.types.'. $state->name))
                        ->columnSpan(1),
                    TextEntry::make('title')
                        ->hiddenLabel()
                        ->columnSpan(3),
                    TextEntry::make('duration')
                        ->hiddenLabel()
                        ->formatStateUsing(fn($state) => DurationEnum::forHumans($state, true))
                        ->icon('heroicon-o-clock')
                        ->columnSpan(2),
                ])->columns(6),
        ];
    }

    public static function unitsSchema(?Model $course): array
    {
        $course->loadMissing(['units.lessons']);

        return [
            RepeatableEntry::make('units')
                ->hiddenLabel()
                ->schema([
                    TextEntry::make('title')
                        ->hiddenLabel()
                        ->icon('heroicon-o-bookmark')
                        ->size(TextEntry\TextEntrySize::Medium)
                        ->weight('font-bold'),
                    RepeatableEntry::make('lessons')
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('type')
                                ->hiddenLabel()
                                ->badge()
                                ->formatStateUsing(fn($state) => __('courses.types.'. $state->name))
                                ->columnSpan(1),
                            TextEntry::make('title')
                                ->hiddenLabel()
                                ->columnSpan(3),
                            TextEntry::make('duration')
                                ->hiddenLabel()
                                ->formatStateUsing(fn($state) => DurationEnum::forHumans($state, true))
                                ->icon('heroicon-o-clock')
                                ->columnSpan(2),
                        ])->columns(6),
                ])->contained(false)->columns(1),
        ];
    }
}
