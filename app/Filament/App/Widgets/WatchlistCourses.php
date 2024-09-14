<?php

namespace App\Filament\App\Widgets;

use App\Filament\App\Resources\CourseResource\Pages\WatchCourse;
use App\Models\Course;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class WatchlistCourses extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->heading(new HtmlString(Blade::render('<div class="flex items-center">
                        <x-heroicon-o-star class="w-6 h-6 text-amber-400" />
                        <span class="ml-2">'.__('Watchlist Courses').'</span>
                    </div>')))
            ->emptyStateHeading(__('Nothing found'))
            ->query(
                fn() => Course::select(['id', 'title', 'cover', 'level'])
                    ->join('course_user', 'course_user.course_id', '=', 'courses.id')
                    ->where('user_id', auth()->id())
                    ->where('watchlist', true)
                    ->whereNull('completed_at')
                    ->limit(6)
            )
            ->columns([
                Split::make([
                    ImageColumn::make('cover')
                        ->translateLabel()
                        ->defaultImageUrl(asset('storage/cover-images/cover_img_1.webp'))
                        ->square()
                        ->grow(false),
                    TextColumn::make('title')
                        ->limit(40)
                        ->translateLabel(),
                ])
            ])
            ->actions([
                TableAction::make(__('Purchase'))
                    ->button()
                    ->visible(fn(Course $record) => !$record->users->first()?->pivot?->purchased_at)
                    ->action(fn(Course $record) => auth()->user()->courses()->syncWithPivotValues([$record->id], ['purchased_at' => now()], false))
                    ->icon('heroicon-s-credit-card')
                    ->color(Color::Lime),
                ViewAction::make()
                    ->label(__('Watch'))
                    ->button()
                    ->color(Color::Lime)
                    ->visible(fn(Course $record) => (bool)$record->users->first()?->pivot?->purchased_at)
                    ->url(fn(Course $record): string => WatchCourse::getUrl(['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}
