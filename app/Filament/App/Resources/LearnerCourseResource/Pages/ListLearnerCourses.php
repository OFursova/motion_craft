<?php

namespace App\Filament\App\Resources\LearnerCourseResource\Pages;

use App\Filament\App\Resources\LearnerCourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearnerCourses extends ListRecords
{
    protected static string $resource = LearnerCourseResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
