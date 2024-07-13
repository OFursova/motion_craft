<?php

namespace App\Filament\Admin\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    protected static ?string $icon = 'heroicon-o-bookmark';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->translateLabel()
                    ->required()
                    ->string()
                    ->maxLength(255)
                    ->notRegex('/&lt;|&gt;|&nbsp;|&amp;|[<>=]+/')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('position')
                    ->translateLabel()
                    ->integer()
                    ->minValue(0),
                Forms\Components\Select::make('course_id')
                    ->translateLabel()
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->translateLabel()
                    ->limit(60)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->translateLabel()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lessons_count')
                    ->label(__('Lessons'))
                    ->counts('lessons'),
                Tables\Columns\TextColumn::make('created_at')
                    ->translateLabel()
                    ->date('d-m-Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->modalWidth('6xl'),
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->modalWidth('6xl'),
                    Tables\Actions\DeleteAction::make(),
                ])->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getModelLabel(): string
    {
        return __('Unit');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Units');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Units');
    }
}
