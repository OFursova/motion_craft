<?php

namespace App\Enums;

use Carbon\CarbonInterval;

enum DurationEnum
{
    public static function forHumans(?string $time = null, bool $short = false): string
    {
        return CarbonInterval::seconds($time)->cascade()->forHumans(short: $short);
    }
}
