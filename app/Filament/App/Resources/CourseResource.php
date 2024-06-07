<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\CourseResource\Pages;
use App\Filament\App\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
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
                        Tables\Columns\ImageColumn::make('cover'),
                        Tables\Columns\TextColumn::make('title')
                            ->weight(FontWeight::Bold)
                            ->size(Tables\Columns\TextColumn\TextColumnSize::Large),
                        Tables\Columns\TextColumn::make('overview')
                            ->html(),
                    ]),
            ])
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->paginationPageOptions([9, 18, 27])
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->visible())
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
