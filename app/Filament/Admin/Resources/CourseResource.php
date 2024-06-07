<?php

namespace App\Filament\Admin\Resources;

use App\Enums\LevelEnum;
use App\Filament\Admin\Resources\CourseResource\Pages;
use App\Filament\Admin\Resources\CourseResource\Pages\CreateCourse;
use App\Filament\Admin\Resources\CourseResource\Pages\EditCourse;
use App\Filament\Admin\Resources\CourseResource\Pages\ListCourses;
use App\Filament\Admin\Resources\CourseResource\RelationManagers;
use App\Filament\Admin\Resources\CourseResource\RelationManagers\LessonsRelationManager;
use App\Filament\Admin\Resources\CourseResource\RelationManagers\UnitsRelationManager;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('title')
                    ->required()
                    ->string()
                    ->maxLength(255)
                    ->notRegex('/&lt;|&gt;|&nbsp;|&amp;|[<>=]+/'),
                Forms\Components\FileUpload::make('cover')
                    ->directory('cover-images')
                    ->image()
                    ->maxSize(1024)
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        null,
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->downloadable(),
                Forms\Components\Textarea::make('overview')
                    ->nullable()
                    ->string()
                    ->maxLength(255)
                    ->notRegex('/&lt;|&gt;|&nbsp;|&amp;|[<>=]+/')
                    ->columnSpan('full'),
                Forms\Components\RichEditor::make('description')
                    ->nullable()
                    ->string()
                    ->maxLength(5000)
                    ->fileAttachmentsDirectory('attachments')
                    ->columnSpan('full'),
                Forms\Components\Fieldset::make('Settings')
                    ->schema([
                        Forms\Components\Select::make('level')
                            ->options(LevelEnum::class)
                            ->nullable(),
                        Forms\Components\Toggle::make('free')
                            ->onIcon('heroicon-o-bolt')
                            ->offIcon('heroicon-o-banknotes')
                            ->onColor('warning'),
                        Forms\Components\Toggle::make('visible')
                            ->onIcon('heroicon-c-eye')
                            ->offIcon('heroicon-c-eye-slash')
                            ->onColor('success'),
                    ])->columns(1)
                    ->columnSpan('sm'),
                Forms\Components\Fieldset::make('Categories')
                    ->schema([
                        Forms\Components\CheckboxList::make('categories')
                            ->label('')
                            ->columns(3)
                            ->relationship('categories', 'name'),
                    ])->columns(1)
                    ->columnSpan('sm'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->limit(50)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->badge()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('cover')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ToggleColumn::make('free')
                    ->onIcon('heroicon-o-bolt')
                    ->offIcon('heroicon-o-banknotes')
                    ->onColor('warning')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('visible')
                    ->onIcon('heroicon-c-eye')
                    ->offIcon('heroicon-c-eye-slash')
                    ->onColor('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('categories.name')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->translateLabel(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->options(LevelEnum::class),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                //ActionGroup::make([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
                Tables\Actions\RestoreAction::make()->iconButton(),
                //])->tooltip('Actions'),
            ], )
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UnitsRelationManager::class,
            LessonsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'create' => CreateCourse::route('/create'),
            'edit' => EditCourse::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPluralModelLabel(): string
    {
        return __('Courses');
    }
}
