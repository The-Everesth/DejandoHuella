<?php

namespace App\Policies;

use App\Models\Clinic;
use App\Models\User;

class ClinicPolicy
{
    public function view(User $user, Clinic $clinic): bool
    {
        return $user->hasRole('admin') || $user->id === $clinic->user_id;
    }

    public function update(User $user, Clinic $clinic): bool
    {
        return $user->hasRole('admin') || $user->id === $clinic->user_id;
    }

    public function delete(User $user, Clinic $clinic): bool
    {
        return $user->hasRole('admin') || $user->id === $clinic->user_id;
    }
}
