<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use App\Services\Utils;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //if (Filament::getCurrentPanel()->getId() === 'admin') {
        //    return $user->is_admin == 1;
        //}

        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Course $course): bool
    {
        return Utils::isAdmin($user) || $course->visible;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return Utils::isAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Course $course): bool
    {
        return Utils::isAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Course $course): bool
    {
        return Utils::isAdmin($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Course $course): bool
    {
        return Utils::isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        return Utils::isAdmin($user);
    }
}
