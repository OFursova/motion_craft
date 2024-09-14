<?php

namespace App\Filament\App\Widgets;

use App\Filament\App\Resources\CourseResource\Pages\WatchCourse;
use App\Models\Course;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Storage;

class ContinueWatching extends Widget
{
    protected static ?int $sort = -2;

    protected static bool $isLazy = false;

    protected static string $view = 'filament.app.widgets.continue-watching';

    protected function getViewData(): array
    {
        $course = Course::select(['courses.id', 'courses.title', 'courses.cover', 'course_lesson.lesson_id'])
            ->join('course_lesson', 'courses.id', '=', 'course_lesson.course_id')
            ->join('lesson_user', 'course_lesson.lesson_id', '=', 'lesson_user.lesson_id')
            ->where('lesson_user.user_id', auth()->id())
            ->join('course_user', 'courses.id', '=', 'course_user.course_id')
            ->where('course_user.user_id', auth()->id())
            ->whereNotNull('lesson_user.completed_at')
            ->whereNull('course_user.completed_at')
            ->orderBy('lesson_user.completed_at', 'desc')
            ->firstOr(fn () => Course::select(['id', 'title', 'cover'])
                ->with('lessons:id')
                ->inRandomOrder()
                ->first()
            );

        $lesson = $course->lesson_id ?? $course->lessons->first()->id;

        return [
            'course' => $course,
            'cover' => Storage::url($course->cover),
            'url' => WatchCourse::getUrl(['record' => $course, 'lesson' => $lesson]),
        ];
    }
}
