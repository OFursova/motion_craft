<?php

namespace App\Filament\App\Resources\CourseResource\Pages;

use App\Enums\DurationEnum;
use App\Filament\App\Resources\CourseResource;
use App\Filament\Entries\PlayerListEntry;
use App\Infolists\Components\VideoPlayerEntry;
use App\Models\Course;
use App\Models\Episode;
use App\Models\Lesson;
use Filament\Actions\Action;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Livewire\Attributes\On;

class WatchCourse extends Page
{
    use InteractsWithRecord;

    public Lesson $lesson;

    protected static string $resource = CourseResource::class;

    protected static string $view = 'filament.app.resources.course-resource.pages.watch-course';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->lesson = $this->record->lessons
            ->where('id', request()->query('lesson'))
            ->first()
            ?? $this->record->lessons->first();
    }

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()->name === 'Admin';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make(__('Back'))
                ->icon('heroicon-o-arrow-uturn-left')
                ->url(ViewCourse::getUrl(['record' => $this->record])),
        ];
    }

    public function watchInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->lesson)
            ->schema([
                Section::make([
                    TextEntry::make('title')
                        ->hiddenLabel()
                        ->size('text-3xl')
                        ->weight('font-bold'),
                    VideoPlayerEntry::make('stream_id')
                        ->hiddenLabel(),
                    TextEntry::make('overview'),
                ]),
            ]);
    }

    public function lessonsInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema(PlayerListEntry::schema($infolist->getRecord(), $this->lesson));
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getHeading(): string
    {
        return '';
    }

    #[On('lesson-ended')]
    public function onLessonEnded(Lesson $lesson): void
    {
        auth()->user()->lessons()->syncWithPivotValues($lesson->id,
            [
                'course_id' => $this->record->id,
                'completed_at' => now()
            ],
            false);

        $currentLessonPosition = $this->record->lessons
            ->where('id', $lesson->id)
            ->first()
            ->position;

        $this->lesson = $this->record->lessons
            ->where('position', $currentLessonPosition + 1)
            ->first() ?? $lesson;
    }
}
