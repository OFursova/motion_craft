<?php

namespace App\Enums;

enum LessonTypeEnum: string
{
    case File = 'file';
    case Quiz = 'quiz';
    case Text = 'text';
    case Video = 'video';

    public static function asArray(): array
    {
        return array_combine(self::getKeys(), self::getValues());
    }

    public static function getKeys(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
