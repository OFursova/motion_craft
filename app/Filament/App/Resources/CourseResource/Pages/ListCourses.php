<?php

namespace App\Filament\App\Resources\CourseResource\Pages;

use App\Filament\App\Resources\CourseResource;
use Filament\Resources\Pages\ListRecords;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
