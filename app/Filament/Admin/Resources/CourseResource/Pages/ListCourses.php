<?php

namespace App\Filament\Admin\Resources\CourseResource\Pages;

use App\Filament\Admin\Resources\CourseResource;
use App\Models\Course;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [];

        $courseCounts = Course::withTrashed()
        ->selectRaw('sum(case when deleted_at is null then 1 else 0 end) as total')
            ->selectRaw('sum(case when level = "beginner" and deleted_at is null then 1 else 0 end) as beginner')
            ->selectRaw('sum(case when level = "intermediate" and deleted_at is null then 1 else 0 end) as intermediate')
            ->selectRaw('sum(case when level = "advanced" and deleted_at is null then 1 else 0 end) as advanced')
            ->selectRaw('sum(case when free = true and deleted_at is null then 1 else 0 end) as free')
            ->selectRaw('sum(case when visible = true and deleted_at is null then 1 else 0 end) as public')
            ->selectRaw('sum(case when deleted_at is not null then 1 else 0 end) as archived')
            ->first();

        $tabs['all'] = Tab::make(__('All'))
            ->badge($courseCounts->total)
            ->icon('heroicon-o-star');

        $tabs['beginner'] = Tab::make(__('courses.levels.Beginner'))
            ->badge($courseCounts->beginner)
            ->modifyQueryUsing(fn ($query) => $query->where('level', 'beginner'))
            ->icon('heroicon-o-book-open');

        $tabs['intermediate'] = Tab::make(__('courses.levels.Intermediate'))
            ->badge($courseCounts->intermediate)
            ->modifyQueryUsing(fn ($query) => $query->where('level', 'intermediate'))
            ->icon('heroicon-o-light-bulb');

        $tabs['advanced'] = Tab::make(__('courses.levels.Advanced'))
            ->badge($courseCounts->advanced)
            ->modifyQueryUsing(fn ($query) => $query->where('level', 'advanced'))
            ->icon('heroicon-s-rocket-launch');

        $tabs['free'] = Tab::make(__('courses.Free'))
            ->badge($courseCounts->free)
            ->modifyQueryUsing(fn ($query) => $query->free())
            ->icon('heroicon-o-bolt');

        $tabs['visible'] = Tab::make(__('courses.Visible'))
            ->badge($courseCounts->public)
            ->modifyQueryUsing(fn ($query) => $query->visible())
            ->icon('heroicon-o-eye');

        $tabs['archived'] = Tab::make(__('courses.Archived'))
            ->badge($courseCounts->archived)
            ->modifyQueryUsing(fn ($query) => $query->onlyTrashed())
            ->icon('heroicon-o-archive-box');

        return $tabs;
    }
}
