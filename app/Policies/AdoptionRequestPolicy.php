<?php

namespace App\Policies;

use App\Models\AdoptionPost;
use App\Models\AdoptionRequest;
use App\Models\User;

class AdoptionRequestPolicy
{
    public function updateStatus(User $user, AdoptionRequest $request): bool
    {
        return $user->id === $request->post->created_by || $user->hasRole('admin');
    }

    /**
     * Autoriza crear una solicitud para un post específico.
     * Nota: esta firma funciona para authorize('create', [AdoptionRequest::class, $post])
     */
    public function create(User $user, AdoptionPost $post): bool
    {
        return $user->id !== $post->created_by;
    }
}
