<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Publishable
{
    public function scopeVisible(Builder $query): void
    {
        $query->where('visible', true);
    }

    public function scopeFree(Builder $query): void
    {
        $query->where('free', true);
    }
}
