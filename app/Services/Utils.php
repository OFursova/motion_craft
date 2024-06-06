<?php

namespace App\Services;

use App\Models\User;

class Utils
{
    public static function isAdmin(?User $user = null): bool
    {
        return in_array($user?->email, config('app.admins'));
    }
}
