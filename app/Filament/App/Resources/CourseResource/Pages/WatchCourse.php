<?php

namespace App\Filament\App\Resources\CourseResource\Pages;

use App\Enums\DurationEnum;
use App\Filament\App\Resources\CourseResource;
use App\Infolists\Components\VideoPlayerEntry;
use App\Models\Course;
use App\Models\Episode;
use App\Models\Lesson;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class WatchCourse extends Page
{
    use InteractsWithRecord;

    public Lesson $lesson;

    protected static string $resource = CourseResource::class;

    protected static string $view = 'filament.app.resources.course-resource.pages.watch-course';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->loadMissing('lessons');

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
            ->schema([
                RepeatableEntry::make('lessons')
                    ->hiddenLabel()
                    ->schema([
                        TextEntry::make('title')
                            ->hiddenLabel()
                            ->icon(fn(Lesson $episode) => $episode->id === 1
                                ? 'heroicon-s-play-circle'
                                : 'heroicon-o-play-circle'
                            )
                            ->iconColor(fn(Lesson $episode) => $episode->id === 1
                                ? 'success'
                                : 'gray'
                            )
                            ->weight(fn(Lesson $episode) => $episode->id === 1
                                ? 'font-bold'
                                : 'font-base'
                            )->columnSpan(5),
                        TextEntry::make('duration')
                            ->hiddenLabel()
                            ->formatStateUsing(fn($state) => DurationEnum::forHumans($state))
                            ->icon('heroicon-o-clock')
                            ->size(TextEntry\TextEntrySize::ExtraSmall)
                            ->columnSpan(2),
                        IconEntry::make('title')
                            ->hiddenLabel()
                            ->icon('heroicon-s-check-circle')
                            ->color('success')
                            //->visible(fn (Lesson $episode): bool => $this->watchedEpisodes->contains('id', $episode->id))
                            ->columnSpan(1),
                    ])->columns(8),
            ]);
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getHeading(): string
    {
        return '';
    }
}
