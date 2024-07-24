<?php

namespace App\Filament\Entries;

use App\Enums\DurationEnum;
use App\Filament\App\Resources\CourseResource\Pages\WatchCourse;
use App\Models\Course;
use App\Models\Lesson;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class PlayerListEntry
{
    public static function schema(Course|Model $course, Lesson $lesson): array
    {
        return $course->loadCount('units')->units_count > 0
            ? self::unitsSchema($course, $lesson)
            : self::itemsSchema($course, $lesson);
    }

    public static function itemsSchema(Course $course, Lesson $currentLesson): array
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
                ->schema([
                    TextEntry::make('title')
                        ->hiddenLabel()
                        ->icon(fn(Lesson $lesson) => $lesson->id === $currentLesson->id
                            ? 'heroicon-s-play-circle'
                            : 'heroicon-o-play-circle'
                        )
                        ->iconColor(fn(Lesson $lesson) => $lesson->id === $currentLesson->id
                            ? 'success'
                            : 'gray'
                        )
                        ->weight(fn(Lesson $lesson) => $lesson->id === $currentLesson->id
                            ? 'font-bold'
                            : 'font-base'
                        )
                        ->url(fn (Lesson $lesson) => route('filament.app.resources.courses.watch', [
                            $course->id,
                            'lesson' => $lesson->id,
                        ]))
                        ->columnSpan(5),
                    TextEntry::make('duration')
                        ->hiddenLabel()
                        ->formatStateUsing(fn($state) => DurationEnum::forHumans($state, true))
                        ->icon('heroicon-o-clock')
                        ->size(TextEntry\TextEntrySize::Small)
                        ->columnSpan(2),
                    IconEntry::make('completed_at')
                        ->hiddenLabel()
                        ->icon('heroicon-s-check-circle')
                        ->color('success')
                        ->visible(fn(Lesson $lesson): bool => (bool) $lesson->completed_at)
                        ->columnSpan(1),
                ])->columns(8),
        ];
    }

    public static function unitsSchema(Course $course, Lesson $currentLesson): array
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
                        ->formatStateUsing(fn($state) => __('Unit') . ' ' . $course->units->where('title', $state)->first()?->id .'. ' . $state)
                        ->icon('heroicon-o-bookmark')
                        ->size(TextEntry\TextEntrySize::Medium)
                        ->weight('font-bold'),
                    RepeatableEntry::make('lessons')
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('title')
                                ->hiddenLabel()
                                ->icon(fn(Lesson $lesson) => $lesson->id === $currentLesson->id
                                    ? 'heroicon-s-play-circle'
                                    : 'heroicon-o-play-circle'
                                )
                                ->iconColor(fn(Lesson $lesson) => $lesson->id === $currentLesson->id
                                    ? 'success'
                                    : 'gray'
                                )
                                ->weight(fn(Lesson $lesson) => $lesson->id === $currentLesson->id
                                    ? 'font-bold'
                                    : 'font-base'
                                )
                                ->url(fn (Lesson $lesson) => route('filament.app.resources.courses.watch', [
                                    $course->id,
                                    'lesson' => $lesson->id,
                                ]))
                                ->columnSpan(5),
                            TextEntry::make('duration')
                                ->hiddenLabel()
                                ->formatStateUsing(fn($state) => DurationEnum::forHumans($state, true))
                                ->icon('heroicon-o-clock')
                                ->size(TextEntry\TextEntrySize::Small)
                                ->columnSpan(2),
                            IconEntry::make('completed_at')
                                ->hiddenLabel()
                                ->icon('heroicon-s-check-circle')
                                ->color('success')
                                ->visible(fn(Lesson $lesson): bool => (bool) $lesson->completed_at)
                                ->columnSpan(1),
                        ])->columns(8),
                ])->contained(false)
                ->columns(1),
        ];
    }
}
