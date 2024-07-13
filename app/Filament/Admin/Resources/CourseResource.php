<?php

namespace App\Filament\Admin\Resources;

use App\Enums\LevelEnum;
use App\Filament\Admin\Resources\CourseResource\Pages\CreateCourse;
use App\Filament\Admin\Resources\CourseResource\Pages\EditCourse;
use App\Filament\Admin\Resources\CourseResource\Pages\ListCourses;
use App\Filament\Admin\Resources\CourseResource\RelationManagers\LessonsRelationManager;
use App\Filament\Admin\Resources\CourseResource\RelationManagers\UnitsRelationManager;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Textarea::make('title')
                            ->translateLabel()
                            ->required()
                            ->string()
                            ->maxLength(255)
                            ->notRegex('/&lt;|&gt;|&nbsp;|&amp;|[<>=]+/')
                            ->live(onBlur: true),
                        //->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                        //    if ($operation !== 'create') {
                        //        return;
                        //    }
                        //    $set('slug', Str::slug($state));
                        //}),
                        Forms\Components\Textarea::make('overview')
                            ->translateLabel()
                            ->nullable()
                            ->string()
                            ->maxLength(255)
                            ->notRegex('/&lt;|&gt;|&nbsp;|&amp;|[<>=]+/')
                            ->columnSpan('full'),
                    ])->columns(1)
                    ->columnSpan('sm'),

                Forms\Components\FileUpload::make('cover')
                    ->translateLabel()
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

                Forms\Components\RichEditor::make('description')
                    ->translateLabel()
                    ->nullable()
                    ->string()
                    ->maxLength(5000)
                    ->fileAttachmentsDirectory('attachments')
                    ->columnSpan('full'),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Select::make('level')
                            ->translateLabel()
                            ->options(LevelEnum::getOptions())
                            ->nullable(),
                        Forms\Components\Toggle::make('free')
                            ->translateLabel()
                            ->onIcon('heroicon-o-bolt')
                            ->offIcon('heroicon-o-banknotes')
                            ->onColor('warning'),
                        Forms\Components\Toggle::make('visible')
                            ->translateLabel()
                            ->onIcon('heroicon-c-eye')
                            ->offIcon('heroicon-c-eye-slash')
                            ->onColor('success'),
                    ])->columns(1)
                    ->columnSpan('sm'),
                Forms\Components\Fieldset::make('Categories')
                    ->translateLabel()
                    ->schema([
                        Forms\Components\CheckboxList::make('categories')
                            ->label('')
                            ->columns(3)
                            ->relationship('categories', 'title'),
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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('title')
                    ->translateLabel()
                    ->limit(50)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->translateLabel()
                    ->formatStateUsing(fn($state) => __('courses.levels.' . $state->name))
                    ->badge()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('cover')
                    ->translateLabel()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ToggleColumn::make('free')
                    ->onIcon('heroicon-o-bolt')
                    ->offIcon('heroicon-o-banknotes')
                    ->onColor('warning')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ToggleColumn::make('visible')
                    ->onIcon('heroicon-c-eye')
                    ->offIcon('heroicon-c-eye-slash')
                    ->onColor('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('categories.title')
                    ->translateLabel()
                    ->badge()
                    ->sortable()
                    ->toggleable()
                    ->visibleFrom('md'),
                Tables\Columns\TextColumn::make('created_at')
                    ->translateLabel()
                    ->dateTime('d-m-Y H:i', 'EEST')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->translateLabel()
                    ->dateTime('d-m-Y H:i', 'EEST')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->translateLabel()
                    ->options(LevelEnum::getOptions()),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
                Tables\Actions\RestoreAction::make()->iconButton(),
            ],)
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

    public static function getModelLabel(): string
    {
        return __('Course');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Courses');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Content Management');
    }
}
