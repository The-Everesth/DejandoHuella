<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function view(User $user, SupportTicket $ticket): bool
    {
        return $user->hasRole('admin') || $user->id === $ticket->user_id;
    }

    public function update(User $user, SupportTicket $ticket): bool
    {
        // el usuario ya no edita tickets; solo admin gestiona
        return $user->hasRole('admin');
    }
}
