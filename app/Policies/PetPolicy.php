<?php

namespace App\Policies;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function view(User $user, Pet $pet)
    {
        return $user->id === $pet->owner_id || $user->hasRole('admin');
    }

    public function update(User $user, Pet $pet)
    {
        return $user->id === $pet->owner_id || $user->hasRole('admin');
    }

    public function delete(User $user, Pet $pet)
    {
        return $user->id === $pet->owner_id || $user->hasRole('admin');
    }

    protected $policies = [
        \App\Models\Pet::class => \App\Policies\PetPolicy::class,
    ];
}
