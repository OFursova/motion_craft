<?php

namespace App\Enums;

use ReflectionClass;

enum LevelEnum: string
{
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';

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
