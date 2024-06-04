<?php


namespace App\Models\Traits;

use Illuminate\Support\Str;

trait Sluggable
{
    protected static function bootSluggable(): void
    {
        static::saving(function ($query) {
            if (($query->name && $query->isDirty('name')) || ($query->title && $query->isDirty('title'))) {

                $field = $query->name ? 'name' : 'title';

                $slug = Str::slug($query->{$field});

                if ($similar = $query::query()->select('id')->where($field, $query->{$field})->count()) {
                    $slug .= '-' . $similar + 1;
                }

                $query->slug = $slug;
            }
        });
    }
}
