<?php

namespace App\Enums;

enum LessonTypeEnum: string
{
    case File = 'file';
    case Quiz = 'Quiz';
    case Text = 'text';
    case Video = 'video';
}
