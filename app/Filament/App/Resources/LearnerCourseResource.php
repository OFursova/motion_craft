<?php

namespace App\Filament\App\Resources;

use App\Enums\CourseTypeEnum;
use App\Enums\DurationEnum;
use App\Enums\LevelEnum;
use App\Filament\App\Resources\CourseResource\Pages\WatchCourse;
use App\Filament\App\Resources\LearnerCourseResource\Pages;
use App\Filament\App\Resources\LearnerCourseResource\RelationManagers;
use App\Filament\Entries\CurriculumEntry;
use App\Infolists\Components\ProgressEntry;
use App\Models\Course;
use App\Models\LearnerCourse;
use App\Services\Converter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LearnerCourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'title';

    protected static int $globalSearchResultsLimit = 20;

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'categories.title'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['categories:id,title']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Grid::make()
                    ->columns(1)
                    ->schema([
                        Grid::make()
                            ->schema([
                                Tables\Columns\TextColumn::make('level')
                                    ->translateLabel()
                                    ->formatStateUsing(fn($state) => __('courses.levels.' . $state->name))
                                    ->badge()
                                    ->color(Color::Zinc)
                                    ->icon('heroicon-o-chart-bar')
                                    ->columnSpan(4),
                                Tables\Columns\TextColumn::make('lessons_sum_duration')
                                    ->default(0)
                                    ->formatStateUsing(fn($state) => Converter::durationInMinutes($state))
                                    ->sum('lessons', 'duration')
                                    ->extraAttributes(['class' => 'px-3'])
                                    ->icon('heroicon-o-clock')
                                    ->columnSpan(3),
                                Tables\Columns\TextColumn::make('lessons_count')
                                    ->formatStateUsing(fn($state) => $state . ' ' . trans_choice('courses.lessons', $state))
                                    ->counts('lessons')
                                    ->icon('heroicon-o-book-open')
                                    ->columnSpan(4),
                                Tables\Columns\IconColumn::make('id')
                                    ->tooltip(fn(Course $record) => __(CourseTypeEnum::getStatus($record, 'tooltip')))
                                    ->color(fn(Course $record) => CourseTypeEnum::getStatus($record, 'color'))
                                    ->icon(fn(Course $record) => CourseTypeEnum::getStatus($record, 'icon')),
                            ])
                            ->columns(12)
                            ->columnSpanFull(),
                        Tables\Columns\ImageColumn::make('cover')
                            ->defaultImageUrl(asset('storage/cover-images/cover_img_1.webp'))
                            ->height(200)
                            ->extraImgAttributes(['class' => 'w-full rounded pt-2']),
                        Grid::make()
                            ->schema([
                                Tables\Columns\TextColumn::make('title')
                                    ->weight(FontWeight::Bold)
                                    ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                                    ->searchable(),
                                Tables\Columns\TextColumn::make('overview')
                                    ->html(),
                                Tables\Columns\TextColumn::make('categories.title')
                                    ->badge(),
                            ])
                            ->extraAttributes(['class' => 'min-h-48 even-content'])
                            ->columns(1)
                            ->columnSpanFull(),
                    ]),
            ])
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->paginationPageOptions([9, 30, 60])
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withAuthUser())
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'watchlisted' => 'Watchlisted',
                        'favorite' => 'Favorite',
                    ])
                    ->query(function (Builder $query, $state) {
                        $query->whereHas('users', function ($query) use ($state) {
                            $query->where('user_id', auth()->id());

                            if ($state['value'] === 'watchlisted') {
                                $query->where('course_user.watchlist', true);
                            } elseif ($state['value'] === 'favorite') {
                                $query->where('course_user.favorite', true);
                            }
                        });
                    }),
            ])
            ->actions([
                TableAction::make('Watchlist')
                    ->hiddenLabel()
                    ->action(fn(Course $record) => $record->users->first()?->pivot?->watchlist
                        ? auth()->user()->watchlistCourses()->updateExistingPivot($record->id, ['watchlist' => false])
                        : auth()->user()->watchlistCourses()->syncWithPivotValues([$record->id], ['watchlist' => true], false))
                    ->button()
                    ->tooltip(fn(Course $record) => $record->users->first()?->pivot?->watchlist ? __('Remove from Watchlist') : __('Add to Watchlist'))
                    ->icon(fn(Course $record) => $record->users->first()?->pivot?->watchlist ? 'heroicon-c-minus' : 'heroicon-c-plus'),
                TableAction::make(__('Purchase'))
                    ->button()
                    ->visible(fn(Course $record) => !$record->users->first()?->pivot?->purchased_at)
                    ->action(fn(Course $record) => auth()->user()->courses()->syncWithPivotValues([$record->id], ['purchased_at' => now()], false))
                    ->icon('heroicon-s-credit-card')
                    ->color(Color::Lime),
                Tables\Actions\ViewAction::make()
                    ->label(__('Watch'))
                    ->button()
                    ->visible(fn(Course $record) => (bool)$record->users->first()?->pivot?->purchased_at)
                    ->color(Color::Lime),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $infolist->getRecord()
            ->loadCount(['lessons', 'units'])
            ->loadCount(['lessons as finished_lessons' => fn(Builder $query) => $query
                ->whereRelation('users', 'id', '=', auth()->id()
                )])
            ->loadSum('lessons', 'duration');

        $totalLessons = $infolist->getRecord()->lessons_count;

        return $infolist
            ->schema([
                Split::make([
                    Section::make()
                        ->headerActions([
                            Action::make('Browse all courses')
                                ->translateLabel()
                                ->url(fn(): string => route('filament.app.resources.courses.index'))
                                ->icon('heroicon-s-arrow-left')
                        ])
                        ->schema([
                            ImageEntry::make('cover')
                                ->hiddenLabel()
                                ->defaultImageUrl(asset('storage/cover-images/cover_img_1.webp'))
                                ->height(200)
                                ->extraImgAttributes(['class' => 'w-full rounded']),
                            TextEntry::make('description')
                                ->hiddenLabel()
                                ->maxWidth(MaxWidth::Small)
                                ->html(),
                            TextEntry::make('categories.title')
                                ->hiddenLabel()
                                ->badge()
                        ])->grow(false),
                    \Filament\Infolists\Components\Grid::make(1)
                        ->schema([
                            Section::make()
                                ->schema([
                                    TextEntry::make('title')
                                        ->hiddenLabel()
                                        ->size('text-3xl')
                                        ->weight('font-bold')
                                        ->columnSpanFull(),
                                    TextEntry::make('overview')
                                        ->hiddenLabel()
                                        ->columnSpanFull(),
                                    ProgressEntry::make('progress')
                                        ->hiddenLabel()
                                        ->progress($totalLessons, $infolist->getRecord()->finished_lessons)
                                        ->columnSpanFull(),

                                    Group::make()
                                        ->schema([
                                            TextEntry::make('finished_lessons')
                                                ->hiddenLabel()
                                                ->formatStateUsing(fn($state) => match ($state) {
                                                    0 => __('courses.not_started'),
                                                    $totalLessons => __('courses.finished_all'),
                                                    default => trans_choice('courses.finished_lessons', $state, ['finished' => $state, 'total' => $totalLessons]),
                                                })
                                                ->icon('heroicon-c-check')
                                                ->iconColor(Color::Lime)
                                                ->columnSpan(2),
                                            TextEntry::make('')
                                                ->columnSpan(1),
                                            TextEntry::make('updated_at')
                                                ->hiddenLabel()
                                                ->badge()
                                                ->prefix(__('Last updated') . ': ')
                                                ->formatStateUsing(fn($state) => $state->isoFormat('Do MMMM Y'))
                                                ->alignRight()
                                                ->columnSpan(2),
                                        ])->columns(5),

                                    Actions::make([
                                        Actions\Action::make('watch')
                                            ->label(fn(Course $course) => auth()->user()->lessons()->where('course_id', $course->id)->exists()
                                                ? __('Continue watching')
                                                : __('Start watching'))
                                            ->button()
                                            ->icon('heroicon-o-play-circle')
                                            ->visible(true)
                                            ->url(WatchCourse::getUrl(['record' => $infolist->getRecord()]))
                                    ]),
                                ]),
                            Section::make('')
                                ->schema([
                                    TextEntry::make('units_count')
                                        ->hiddenLabel()
                                        ->formatStateUsing(fn($state) => $state . ' ' . trans_choice('courses.units', $state))
                                        ->icon('heroicon-o-bookmark'),
                                    TextEntry::make('lessons_count')
                                        ->hiddenLabel()
                                        ->formatStateUsing(fn($state) => $state . ' ' . trans_choice('courses.lessons', $state))
                                        ->icon('heroicon-o-book-open'),
                                    TextEntry::make('lessons_sum_duration')
                                        ->formatStateUsing(fn($state) => DurationEnum::forHumans($state, true) ?: __('0m'))
                                        ->hiddenLabel()
                                        ->icon('heroicon-o-clock'),
                                    TextEntry::make('level')
                                        ->hiddenLabel()
                                        ->formatStateUsing(fn($state) => __('courses.levels.' . $state->name))
                                        ->icon('heroicon-o-chart-bar'),
                                ])->columns(4),
                            Section::make(__('Curriculum'))
                                ->collapsible()
                                ->schema(CurriculumEntry::schema($infolist->getRecord())),
                        ])
                ])
                    ->from('md')
                    ->columnSpanFull(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLearnerCourses::route('/'),
            'view' => Pages\ViewCourse::route('/{record}'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Course');
    }

    public static function getPluralModelLabel(): string
    {
        return __('My Courses');
    }

    public static function getNavigationLabel(): string
    {
        return __('My Courses');
    }
}
