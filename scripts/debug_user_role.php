<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use App\Services\Firestore\FirestoreUserRoleService;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = Auth::user();
if (!$user) {
    echo "No hay usuario autenticado.\n";
    exit(1);
}

$id = $user->id;
echo "Usuario autenticado: ID={$id}, email={$user->email}\n";

$roles = App::make(FirestoreUserRoleService::class)->getRolesByLaravelUserId($id);
echo "Roles Firestore: ".json_encode($roles)."\n";

if (in_array('admin', $roles, true)) {
    echo "El usuario tiene rol admin.\n";
} else {
    echo "El usuario NO tiene rol admin.\n";
}
