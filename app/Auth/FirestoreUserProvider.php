<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use App\Models\FirestoreAuthenticatableUser;
use App\Services\Firestore\UsersFirestoreService;

class FirestoreUserProvider implements UserProvider
{
    protected $firestore;

    public function __construct(UsersFirestoreService $firestore)
    {
        $this->firestore = $firestore;
    }

    public function retrieveById($identifier)
    {
        // Buscar por el ID real de Firestore (string)
        $data = $this->firestore->getUserByDocId((string)$identifier);
        return $data ? new FirestoreAuthenticatableUser($data) : null;
    }

    public function retrieveByToken($identifier, $token)
    {
        // Opcional: implementar si usas remember_token
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // Opcional: implementar si usas remember_token
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials['email'])) {
            return null;
        }
        $users = $this->firestore->list();
        foreach ($users as $data) {
            if (isset($data['email']) && strtolower($data['email']) === strtolower($credentials['email'])) {
                return new FirestoreAuthenticatableUser($data);
            }
        }
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // Si tienes hash de password en Firestore
        if (isset($user->password) && isset($credentials['password'])) {
            return Hash::check($credentials['password'], $user->password);
        }
        return false;
    }
}
