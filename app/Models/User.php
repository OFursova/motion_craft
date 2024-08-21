<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\Utils;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'customer_id',
        'ip',
        'is_admin',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return Utils::isAdmin($this);
        }

        if ($panel->getId() === 'app') {
            return auth()->check();
        }

        return false;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return Storage::url($this->avatar_url);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)
            ->withPivot(['favorite', 'watchlist', 'purchased_at', 'completed_at'])
            ->whereNotNull('purchased_at');
    }

    public function favoriteCourses(): BelongsToMany
    {
        return $this->courses()->where('favorite', true);
    }

    public function finishedCourses(): BelongsToMany
    {
        return $this->courses()->whereNotNull('completed_at');
    }

    public function watchlistCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)
            ->withPivot(['favorite', 'watchlist', 'purchased_at', 'completed_at'])
            ->where('watchlist', true);
    }

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class)
            ->withPivot(['course_id', 'completed_at'])
            ->orderBy('lesson_user.completed_at');
    }
}
