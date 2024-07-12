<?php

namespace App\Filament\App\Resources;

use App\Enums\DurationEnum;
use App\Enums\LevelEnum;
use App\Filament\App\Resources\CourseResource\Pages;
use App\Filament\App\Resources\CourseResource\RelationManagers;
use App\Filament\Entries\CurriculumEntry;
use App\Models\Course;
use App\Models\Episode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'categories.name'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Grid::make()
                    ->columns(1)
                    ->schema([
                        Tables\Columns\TextColumn::make('level')
                            ->translateLabel()
                            ->formatStateUsing(fn($state) => __('courses.levels.'. $state->name))
                            ->badge(),
                        Tables\Columns\ImageColumn::make('cover')
                            ->defaultImageUrl(asset('storage/cover-images/cover_img_1.webp'))
                            ->height(200)
                            ->extraImgAttributes(['class' => 'w-full rounded']),
                        Tables\Columns\TextColumn::make('title')
                            ->weight(FontWeight::Bold)
                            ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                            ->searchable(),
                        Tables\Columns\TextColumn::make('overview')
                            ->html(),
                        Tables\Columns\TextColumn::make('categories.name')
                            ->badge(),
                    ])->extraAttributes(['class' => 'justify-between']),
            ])
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->paginationPageOptions([9, 30, 60])
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->visible())
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->options(LevelEnum::class),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('categories', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('free'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->button()
                    ->color(Color::Lime),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $infolist->getRecord()
            ->loadCount(['lessons', 'units'])
            ->loadSum('lessons', 'duration');

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
                            TextEntry::make('categories.name')
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
                                    TextEntry::make('updated_at')
                                        ->hiddenLabel()
                                        ->badge()
                                        ->prefix(__('Last updated') . ': ')
                                        ->formatStateUsing(fn($state) => $state->isoFormat('Do MMMM Y'))
                                        ->alignRight()
                                        ->lineClamp(2),
                                    // has been started flag
                                    Actions::make([
                                        Actions\Action::make('watch')
//                                            ->label(fn (Course $course) => auth()->user()->watchedEpisodes()->where('course_id', $course->id)->exists()
//                                                ? 'Continue watching'
//                                                : 'Start watching')
                                            ->button()
                                            ->icon('heroicon-o-play-circle')
                                            ->visible(true)
                                            ->action(fn (Course $course) => $this->redirectRoute('courses.episodes.show', ['course' => $course]))
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
            'index' => Pages\ListCourses::route('/'),
            'view' => Pages\ViewCourse::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return Cache::remember('course_badge', 3 * 60 * 60,
            fn() => static::getModel()::where('created_at', '<', today()->subWeek())->exists() ? 'NEW' : null);
    }

    public static function getModelLabel(): string
    {
        return __('Course');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Courses');
    }
}
