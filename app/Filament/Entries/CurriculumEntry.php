<?php

namespace App\Filament\Entries;

use App\Enums\DurationEnum;
use App\Filament\App\Resources\CourseResource\Pages\WatchCourse;
use App\Models\Lesson;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        $course->loadMissing(['lessons' => fn (BelongsToMany $query) => $query
            ->addSelect(['completed_at' => \DB::table('lesson_user')
                ->select('completed_at')
                ->where('user_id', auth()->id())
                ->where('course_id', $course->id)
                ->whereColumn('lesson_id', '=', 'lessons.id')
            ])
        ]);

        return [
            RepeatableEntry::make('lessons')
                ->hiddenLabel()
                ->schema(self::lessonsSchema($course))
                ->columns(12),
        ];
    }

    public static function unitsSchema(?Model $course): array
    {
        $course->loadMissing(['units.lessons' => fn (BelongsToMany $query) => $query
            ->addSelect(['completed_at' => \DB::table('lesson_user')
                ->select('completed_at')
                ->where('user_id', auth()->id())
                ->where('course_id', $course->id)
                ->whereColumn('lesson_id', '=', 'lessons.id')
            ])
        ]);

        return [
            RepeatableEntry::make('units')
                ->hiddenLabel()
                ->schema([
                    TextEntry::make('title')
                        ->hiddenLabel()
                        ->formatStateUsing(fn($state) => __('Unit') . ' ' . $course->units->where('title', $state)->first()?->position .'. ' . $state)
                        ->icon('heroicon-o-bookmark')
                        ->size(TextEntry\TextEntrySize::Medium)
                        ->weight('font-bold'),
                    RepeatableEntry::make('lessons')
                        ->hiddenLabel()
                        ->schema(self::lessonsSchema($course))
                        ->columns(12),
                ])->contained(false)
                ->columns(1),
        ];
    }

    public static function lessonsSchema(?Model $course): array
    {
        return [
            IconEntry::make('type')
                ->hiddenLabel()
                ->tooltip(fn($state) => __('courses.types.' . $state->name))
                ->size(IconEntry\IconEntrySize::ExtraLarge)
                ->columnSpan(1),
            TextEntry::make('title')
                ->hiddenLabel()
                ->url(fn(Lesson $lesson) => WatchCourse::getUrl(['record' => $course, 'lesson' => $lesson->id ?? 1]))
                ->columnSpan(7),
            TextEntry::make('duration')
                ->hiddenLabel()
                ->formatStateUsing(fn($state) => DurationEnum::forHumans($state, true))
                ->icon('heroicon-o-clock')
                ->columnSpan(3),
            IconEntry::make('icon')
                ->hiddenLabel()
                ->tooltip(__('courses.watched'))
                ->boolean()
                ->default(fn (Lesson $lesson) => (bool) $lesson->completed_at)
                ->trueIcon('heroicon-o-check-badge')
                ->falseIcon('heroicon-o-play')
                ->falseColor(Color::Indigo),
        ];
    }
}
