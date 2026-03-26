<?php

namespace App\Services\Firestore;

use App\Models\User;

class FirestoreUserRoleService
{
    protected $client;

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }


    /**
     * Obtiene el documento de usuario compatible con IDs automáticos y antiguos.
     * Si el usuario tiene user_code, busca por ese campo; si no, por el document ID real.
     */
    public function getUserDocFlexible($user): ?array
    {
        // Si el usuario tiene user_code, buscar por ese campo
        if (isset($user->user_code)) {
            $users = $this->client->listDocs('users');
            foreach ($users as $doc) {
                if (isset($doc['user_code']) && $doc['user_code'] === $user->user_code) {
                    return $doc;
                }
            }
        }
        // Si no, buscar por el document ID real
        if (isset($user->id)) {
            $doc = $this->client->getDoc('users', (string)$user->id);
            if ($doc) return $doc;
        }
        // Fallback: buscar por u_# para compatibilidad
        if (isset($user->laravelUserId)) {
            $docId = 'u_' . $user->laravelUserId;
            $doc = $this->client->getDoc('users', $docId);
            if ($doc) return $doc;
        }
        return null;
    }

    /**
     * Obtiene los roles del usuario autenticado (flexible para ambos tipos de ID).
     */
    public function getRolesByUser($user): array
    {
        $doc = $this->getUserDocFlexible($user);
        if (! $doc) {
            return [];
        }
        $roles = [];
        if (isset($doc['role']) && is_string($doc['role']) && trim($doc['role']) !== '') {
            $roles[] = strtolower(trim($doc['role']));
        }
        return array_values(array_unique($roles));
    }

    public function hasRoleByUser($user, $requestedRoles): bool
    {
        $userRoles = $this->getRolesByUser($user);
        if (empty($userRoles)) {
            return false;
        }
        $normalizedRequested = $this->normalizeRequestedRoles($requestedRoles);
        if (empty($normalizedRequested)) {
            return false;
        }
        foreach ($normalizedRequested as $role) {
            if (in_array($role, $userRoles, true)) {
                return true;
            }
        }
        return false;
    }

    public function syncPrimaryRole(User $user, string $role): void
    {
        $role = strtolower(trim($role));
        if ($role === '') {
            return;
        }

        $docId = 'u_'.$user->id;
        $docPath = 'users/'.$docId;
        $now = now()->toIso8601String();

        $exists = $this->client->getDocument($docPath);

        $payload = [
            'laravelUserId' => (int) $user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'role' => $role,
            'status' => 'active',
            'updatedAt' => $now,
        ];

        if ($exists) {
            $this->client->patchDocument($docPath, $payload);
            return;
        }

        $this->client->createDocument('users', $docId, $payload);
    }

    public function setPendingRoleRequest(User $user, string $requestedRole): void
    {
        $this->syncPrimaryRole($user, 'ciudadano');
    }

    public function resolveRoleRequest(User $user, string $status, string $effectiveRole = null): void
    {
        $effectiveRole = is_string($effectiveRole) && trim($effectiveRole) !== ''
            ? strtolower(trim($effectiveRole))
            : 'ciudadano';

        $this->syncPrimaryRole($user, $effectiveRole);
    }

    protected function normalizeRequestedRoles($requestedRoles): array
    {
        $flattened = [];

        if (is_string($requestedRoles)) {
            $parts = preg_split('/[|,]/', $requestedRoles) ?: [];
            foreach ($parts as $part) {
                $flattened[] = $part;
            }
        } elseif (is_array($requestedRoles)) {
            foreach ($requestedRoles as $item) {
                if (is_array($item)) {
                    foreach ($item as $nested) {
                        $flattened[] = $nested;
                    }
                } else {
                    $flattened[] = $item;
                }
            }
        } else {
            $flattened[] = $requestedRoles;
        }

        $out = [];
        foreach ($flattened as $role) {
            if (is_string($role) && trim($role) !== '') {
                $out[] = strtolower(trim($role));
            }
        }

        return array_values(array_unique($out));
    }
}
