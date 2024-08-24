<?php

namespace App\Infolists\Components;

use Filament\Infolists\Components\Entry;

class ProgressEntry extends Entry
{
    protected string $view = 'infolists.components.progress-entry';

    protected int $progress;

    public function progress(int $total, int $finished): static
    {
        $this->progress = number_format($finished * 100 / ($total > 0 ? $total : 1));

        return $this;
    }

    public function getProgress(): ?int
    {
        return $this->evaluate($this->progress);
    }
}
