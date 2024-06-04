<?php

namespace App\Services;

use Carbon\CarbonInterval;

class Converter
{
    public static function durationInMinutes(int $seconds): string
    {
        return self::trimHours(self::secondsToString($seconds));
}
    public static function secondsToHours(?string $time): int
    {
        return $time ? (int) ceil($time / 60 / 60) : 0;
    }

    public static function secondsToString(?string $time): string
    {
        if (! $time) {
            return '00:00:00';
        }

        return sprintf('%02d:%02d:%02d', $time / 3600, ($time % 3600) / 60, ($time % 3600) % 60);
    }

    public static function stringToSeconds(string $duration): string
    {
        return CarbonInterval::createFromFormat('H:i:s', $duration)->totalSeconds;
    }

    public static function trimHours(string $duration): string
    {
        return str_starts_with($duration, '00:') ? substr($duration, 3) : $duration;
    }

    public static function trimSeconds(string $duration): ?string
    {
        return substr_replace($duration, '', -3);
    }
}
