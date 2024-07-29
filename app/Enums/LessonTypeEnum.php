<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LessonTypeEnum: string implements HasLabel, HasIcon, HasColor
{
    case File = 'file';
    case Quiz = 'quiz';
    case Text = 'text';
    case Video = 'video';

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::File => 'heroicon-s-document-arrow-down',
            self::Quiz => 'heroicon-o-clipboard-document-check',
            self::Text => 'heroicon-o-document-text',
            self::Video => 'heroicon-c-play-circle',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::File => Color::Amber,
            self::Quiz => Color::Violet,
            self::Text => Color::Cyan,
            self::Video => Color::Emerald,
        };
    }
}
