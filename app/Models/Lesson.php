<?php

namespace App\Models;

use App\Enums\LessonTypeEnum;
use App\Services\Converter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lesson extends Model
{
    use HasFactory;

    protected $appends = [
        'duration_in_minutes',
    ];

    protected $fillable = [
        'title',
        'type',
        'overview',
        'url',
        'content',
        'duration',
        'free',
        'visible',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'free' => 'boolean',
            'type' => LessonTypeEnum::class,
            'visible' => 'boolean',
        ];
    }

    protected function durationInMinutes(): Attribute
    {
        return Attribute::make(
            get: fn () => Converter::durationInMinutes($this->duration),
        );
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)
            ->withPivot(['position', 'unit_id'])
            ->orderBy('course_lesson.position');
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'course_lesson', 'lesson_id', 'unit_id')
            ->withPivot(['position', 'course_id'])
            ->orderBy('course_lesson.position');
    }
}
