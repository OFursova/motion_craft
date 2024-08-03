<?php

namespace App\Filament\App\Resources\CourseResource\Pages;

use App\Filament\App\Resources\CourseResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    protected ?string $heading = '';

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getTitle(): string|Htmlable
    {
        return self::getRecord()?->title;
    }
}
