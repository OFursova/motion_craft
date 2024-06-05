<?php

namespace App\Filament\Resources;

use App\Enums\LevelEnum;
use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use phpDocumentor\Reflection\Types\Boolean;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->string()
                    ->maxLength(255)
                    ->notRegex('/&lt;|&gt;|&nbsp;|&amp;|[<>=]+/')
                    ->columnSpan('full'),
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
                            ->options(array_flip(LevelEnum::asArray()))
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
                    ->limit(60)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('cover'),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->options(array_flip(LevelEnum::asArray()))
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UnitsRelationManager::class,
            RelationManagers\LessonsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
