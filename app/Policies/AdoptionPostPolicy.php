<?php

namespace App\Policies;

use App\Models\AdoptionPost;
use App\Models\User;

class AdoptionPostPolicy
{
    public function update(User $user, AdoptionPost $post): bool
    {
        return $user->id === $post->created_by || $user->hasRole('admin');
    }

    public function delete(User $user, AdoptionPost $post): bool
    {
        return $user->id === $post->created_by || $user->hasRole('admin');
    }

    public function viewRequests(User $user, AdoptionPost $post): bool
    {
        return $user->id === $post->created_by || $user->hasRole('admin');
    }
}
