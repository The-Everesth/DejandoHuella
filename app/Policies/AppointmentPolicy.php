<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
        return $user->hasRole('admin')
            || $user->id === $appointment->owner_id
            || $user->id === $appointment->vet_id
            || $user->id === $appointment->clinic->owner_vet_id;
    }

    public function updateStatus(User $user, Appointment $appointment): bool
    {
        return $user->hasRole('admin')
            || $user->id === $appointment->vet_id
            || $user->id === $appointment->clinic->owner_vet_id;
    }
}
