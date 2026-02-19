<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Services\Firestore\UsersFirestoreService;

class SyncUserToFirestore
{
    public function __construct(private UsersFirestoreService $usersService)
    {
    }

    public function handle(Registered $event): void
    {
        // Sincroniza el usuario recién registrado a Firestore
        $this->usersService->syncFromUser($event->user);
    }
}
