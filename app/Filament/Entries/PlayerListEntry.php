<?php

namespace App\Filament\Entries;

use App\Enums\DurationEnum;
use App\Filament\App\Resources\CourseResource\Pages\ViewCourse;
use App\Filament\App\Resources\CourseResource\Pages\WatchCourse;
use App\Models\Course;
use App\Models\Lesson;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
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
            Actions::make([
                Action::make(__('Back'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->url(ViewCourse::getUrl(['record' => $course])),
            ])->alignment(Alignment::Right),
            RepeatableEntry::make('lessons')
                ->hiddenLabel()
                ->schema(self::lessonsSchema($course, $currentLesson))
                ->columns(12),
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
                        ->schema(self::lessonsSchema($course, $currentLesson))
                        ->columns(12),
                ])->contained(false)
                ->columns(1),
        ];
    }

    public static function lessonsSchema(Course $course, Lesson $currentLesson): array
    {
        return [
            IconEntry::make('type')
                ->hiddenLabel()
                ->color(fn (Lesson $lesson) => $lesson->id === $currentLesson->id
                    ? 'success'
                    : 'gray'
                )
                ->icon(function (Lesson $lesson) {
                    if ($lesson->completed_at) {
                        return 'heroicon-o-check-circle';
                    }
                })
                ->tooltip(fn($state) => __('courses.types.' . $state->name))
                ->size(IconEntry\IconEntrySize::ExtraLarge)
                ->columnSpan(1),
            TextEntry::make('title')
                ->hiddenLabel()
                ->weight(fn (Lesson $lesson) => $lesson->id === $currentLesson->id
                    ? 'font-bold'
                    : 'font-base'
                )
                ->url(fn (Lesson $lesson) => route('filament.app.resources.courses.watch', [
                    $course->id,
                    'lesson' => $lesson->id,
                ]))
                ->columnSpan(8),
            TextEntry::make('duration')
                ->hiddenLabel()
                ->formatStateUsing(fn ($state) => DurationEnum::forHumans($state, true))
                ->icon('heroicon-o-clock')
                ->size(TextEntry\TextEntrySize::Small)
                ->columnSpan(3),
        ];
    }
}
