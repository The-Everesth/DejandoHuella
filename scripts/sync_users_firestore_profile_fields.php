<?php
// scripts/sync_users_firestore_profile_fields.php

use App\Models\User;
use App\Services\Firestore\UsersFirestoreService;

require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = User::all();
$firestore = app(UsersFirestoreService::class);

$updated = 0;
foreach ($users as $user) {
    // Si el usuario no tiene los campos, inicializarlos como null
    $changed = false;
    if (!isset($user->profile_photo_path)) {
        $user->profile_photo_path = null;
        $changed = true;
    }
    if (!isset($user->profile_photo_url)) {
        $user->profile_photo_url = null;
        $changed = true;
    }
    if ($changed) {
        $user->save();
    }
    // Sincronizar con Firestore
    $firestore->syncFromUser($user);
    $updated++;
    echo "Sincronizado usuario ID {$user->id}\n";
}
echo "Usuarios sincronizados: $updated\n";
