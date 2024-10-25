<?php

namespace App\Filament\App\Widgets;

use App\Filament\App\Resources\CourseResource\Pages\WatchCourse;
use App\Models\Course;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class FavoriteCourses extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->heading(new HtmlString(Blade::render('<div class="flex items-center">
                        <x-heroicon-s-heart class="w-6 h-6 text-red-700" />
                        <span class="ml-2">'.__('Favorites').'</span>
                    </div>')))
            ->emptyStateHeading(__('Nothing found'))
            ->query(
                fn() => Course::select(['id', 'title', 'cover', 'level'])
                    ->join('course_user', 'course_user.course_id', '=', 'courses.id')
                    ->where('user_id', auth()->id())
                    //->whereNotNull('purchased_at')
                    ->where('favorite', true)
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
                ViewAction::make()
                    ->label(__('Watch'))
                    ->button()
                    ->icon('heroicon-s-play')
                    ->color(Color::Lime)
                    ->url(fn(Course $record): string => WatchCourse::getUrl(['record' => $record])),
            ])
            ->paginated(false);
    }
}
