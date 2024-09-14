<?php

namespace App\Models;

use App\Enums\LevelEnum;
use App\Models\Traits\Publishable;
use App\Models\Traits\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    use HasFactory, Publishable, Sluggable, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'overview',
        'description',
        'cover',
        'level',
        'free',
        'visible',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'free' => 'boolean',
            'level' => LevelEnum::class,
            'visible' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::deleting(function ($query) {
            if ($query->cover) {
                Storage::delete($query->getRawOriginal('cover'));
            }
        });
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class)
            ->withPivot(['position', 'unit_id'])
            ->orderBy('course_lesson.position');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class)->orderBy('position');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['favorite', 'watchlist', 'purchased_at', 'completed_at']);
    }

    public function scopeBelongsToAuthUser(Builder $query): void
    {
        $query->visible()
            ->whereHas('users', fn ($query) => $query->where('id', auth()->id()));
    }

    public function scopeVisible(Builder $query): void
    {
        $query->where('visible', true);
    }

    public function scopeWithAuthUser(Builder $query): void
    {
        $query->visible()
            ->withWhereHas('users', fn ($query) => $query
                ->select(['id', 'name', 'avatar_url'])
                ->where('id', auth()->id())
            );
    }
}
