<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use App\Enums\LessonTypeEnum;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Unit;
use App\Services\Converter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
                Forms\Components\Select::make('type')
                    ->options(array_flip(LessonTypeEnum::asArray()))
                    ->nullable(),
                Forms\Components\TextInput::make('duration')
                    ->placeholder('00:00:00')
                    ->regex('/^([01]\d|2[0-3]):[0-5]\d:[0-5]\d$/')
                    ->formatStateUsing(fn (string $state): string => Converter::secondsToString($state))
                    ->dehydrateStateUsing(fn (string $state): string => Converter::stringToSeconds($state)),
                Forms\Components\TextInput::make('url')
                    ->nullable()
                    ->string()
                    ->maxLength(255)
                    ->url()
                    ->columnSpan('full'),
                Forms\Components\RichEditor::make('content')
                    ->nullable()
                    ->string()
                    ->maxLength(65535)
                    ->columnSpan('full')
                    ->fileAttachmentsDirectory('attachments'),
                Forms\Components\Fieldset::make('Attached')
                    ->schema([
                        Forms\Components\Select::make('unit_id')
                            ->label('Unit')
                            ->options(fn() => Unit::select(['id', 'title'])->where('course_id', $this->ownerRecord?->id)->pluck('title', 'id'))
                            ->createOptionForm([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->string()
                                    ->maxLength(255)
                                    ->notRegex('/&lt;|&gt;|&nbsp;|&amp;|[<>=]+/'),
                                Forms\Components\TextInput::make('position')
                                    ->integer()
                                    ->minValue(0),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return $this->ownerRecord->units()->create($data)->getKey();
                            })
                            ->placeholder('Choose a unit'),
                        Forms\Components\TextInput::make('position')
                            ->integer()
                            ->minValue(0),
                    ])->columnSpan('sm'),
                Forms\Components\Fieldset::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('free')
                            ->onIcon('heroicon-o-bolt')
                            ->offIcon('heroicon-o-banknotes')
                            ->onColor('warning'),
                        Forms\Components\Toggle::make('visible')
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
                    ->limit(60)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration')
                    ->state(fn(Lesson $record) => Converter::durationInMinutes($record->duration)),
                Tables\Columns\TextColumn::make('position')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_id')
                    ->state(function (Lesson $record) {
                        $units = Cache::remember('units', 5 * 60, fn() => Unit::select(['id', 'title'])->get());
                        return $units->where('id', $record->unit_id)->first()->title;
                    })
                    ->label('Unit')
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('type')
                    ->options(array_flip(LessonTypeEnum::asArray()))
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make(),
                Tables\Actions\CreateAction::make()->modalWidth('6xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modalWidth('6xl'),
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
}
