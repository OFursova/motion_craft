<?php

namespace App\Filament\Admin\Resources\CourseResource\RelationManagers;

use App\Enums\LessonTypeEnum;
use App\Models\Lesson;
use App\Models\Unit;
use App\Services\Converter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $icon = 'heroicon-o-academic-cap';

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
                    ->columnSpan('full'),
                Forms\Components\Textarea::make('overview')
                    ->translateLabel()
                    ->nullable()
                    ->string()
                    ->maxLength(255)
                    ->notRegex('/&lt;|&gt;|&nbsp;|&amp;|[<>=]+/')
                    ->columnSpan('full'),
                Forms\Components\Select::make('type')
                    ->translateLabel()
                    ->options(LessonTypeEnum::class)
                    ->nullable(),
                Forms\Components\TextInput::make('duration')
                    ->translateLabel()
                    ->placeholder('00:00:00')
                    ->regex('/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/')
                    ->formatStateUsing(fn (?string $state): string => Converter::secondsToString($state))
                    ->dehydrateStateUsing(fn (string $state): string => Converter::stringToSeconds($state)),
                Forms\Components\TextInput::make('url')
                    ->translateLabel()
                    ->nullable()
                    ->string()
                    ->maxLength(255)
                    ->url()
                    ->columnSpan('full'),
                Forms\Components\RichEditor::make('content')
                    ->translateLabel()
                    ->nullable()
                    ->string()
                    ->maxLength(65535)
                    ->columnSpan('full')
                    ->fileAttachmentsDirectory('attachments'),
                Forms\Components\Fieldset::make(__('Attached'))
                    ->schema([
                        Forms\Components\Select::make('unit_id')
                            ->label(__('Unit'))
                            ->options(fn() => Unit::select(['id', 'title'])
                                ->where('course_id', $this->ownerRecord?->id)
                                ->pluck('title', 'id')
                            )
                            ->createOptionForm([
                                Forms\Components\TextInput::make('title')
                                    ->translateLabel()
                                    ->required()
                                    ->string()
                                    ->maxLength(255)
                                    ->notRegex('/&lt;|&gt;|&nbsp;|&amp;|[<>=]+/'),
                                Forms\Components\TextInput::make('position')
                                    ->translateLabel()
                                    ->integer()
                                    ->minValue(0),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return $this->ownerRecord->units()->create($data)->getKey();
                            })
                            ->placeholder(__('Choose a unit')),
                        Forms\Components\TextInput::make('position')
                            ->translateLabel()
                            ->integer()
                            ->minValue(0),
                    ])->columnSpan('sm'),
                Forms\Components\Fieldset::make(__('Settings'))
                    ->schema([
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
                    ])->columns(3)
                    ->columnSpan('sm'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->translateLabel()
                    ->limit(40)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->translateLabel()
                    ->formatStateUsing(fn($state) => __('courses.types.'. $state->name))
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration')
                    ->translateLabel()
                    ->state(fn(Lesson $record) => Converter::durationInMinutes($record->duration))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('position')
                    ->translateLabel()
                    ->numeric()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('units.position')
                    ->label(__('Unit'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('units.title')
                    ->label(__('Unit name'))
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('free')
                    ->onIcon('heroicon-o-bolt')
                    ->offIcon('heroicon-o-banknotes')
                    ->onColor('warning')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('visible')
                    ->onIcon('heroicon-c-eye')
                    ->offIcon('heroicon-c-eye-slash')
                    ->onColor('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->translateLabel()
                    ->date('d-m-Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(LessonTypeEnum::class)
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make(),
                Tables\Actions\CreateAction::make()->modalWidth('6xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->modalWidth('6xl'),
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->modalWidth('6xl'),
                    Tables\Actions\DetachAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Lessons');
    }
}
