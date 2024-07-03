<?php

namespace App\Filament\App\Resources;

use App\Enums\DurationEnum;
use App\Enums\LevelEnum;
use App\Filament\App\Resources\CourseResource\Pages;
use App\Filament\App\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use App\Models\Episode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

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
                    ])->extraAttributes(['class' => 'justify-between']),
            ])
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->paginationPageOptions([9, 18, 27])
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
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        ImageEntry::make('cover')
                            ->hiddenLabel()
                            ->defaultImageUrl(asset('storage/cover-images/cover_img_1.webp'))
                            ->height(200)
                            ->extraImgAttributes(['class' => 'w-full rounded']),
                        TextEntry::make('description')
                            ->hiddenLabel(),
                    ]),
                Section::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextEntry::make('title')
                                    ->hiddenLabel()
                                    ->size('text-4xl')
                                    ->weight('font-bold')
                                    ->columnSpanFull(),
                                TextEntry::make('overview')
                                    ->hiddenLabel()
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Insights')
                            ->schema([
                                TextEntry::make('lessons_count'),
                                TextEntry::make('duration'),
                                TextEntry::make('level')
                                    ->hiddenLabel(),
                            ])->columns(6),
                        Section::make('About this course')
                            ->schema([
                                RepeatableEntry::make('lessons')
                                    ->schema([
                                        TextEntry::make('title')
                                            ->hiddenLabel()
//                                    ->url(fn (Episode $episode) => route('courses.episodes.show', [
//                                        'course' => $this->course,
//                                        'episode' => $episode,
//                                    ]))
                                            ->icon('heroicon-o-play-circle'),
                                        TextEntry::make('duration')
                                            ->hiddenLabel()
                                            ->formatStateUsing(fn($state) => '0s')
                                            ->icon('heroicon-o-clock'),
                                    ])->columns(2)
                            ])->columns(2),
                    ])
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
        return static::getModel()::where('created_at', '<', today()->subWeek())->exists()
            ? 'NEW'
            : null;
    }

    public static function getNavigationLabel(): string
    {
        return __('Courses');
    }
}
