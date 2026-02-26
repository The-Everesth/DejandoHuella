<?php

namespace App\Services\Firestore;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UsersFirestoreService
{
    protected FirestoreRestClient $client;
    protected string $collection = 'users';

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    /**
     * Sincroniza un usuario de Laravel a Firestore.
     * Idempotente: si existe, hace PATCH; si no, CREATE.
     */
    public function syncFromUser(User $user): array
    {
        try {
            $docId = $this->getUserDocId($user->id);
            
            $data = [
                'laravelUserId'  => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'role'           => $this->getRole($user),
                'status'         => $user->status ?? 'active',
                'createdAt'      => $user->created_at->toIso8601String(),
                'updatedAt'      => $user->updated_at->toIso8601String(),
            ];

            // createDoc es idempotente: si existe lo patchea, si no lo crea
            $result = $this->client->createDoc($this->collection, $docId, $data);

            Log::info('Usuario sincronizado a Firestore', [
                'laravelUserId' => $user->id,
                'firestoreDocId' => $docId,
            ]);

            return [
                'success' => true,
                'laravelUserId' => $user->id,
                'firestoreId' => $docId,
                'data' => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('Error sincronizando usuario a Firestore', [
                'laravelUserId' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'laravelUserId' => $user->id,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtiene un usuario desde Firestore por Laravel ID.
     */
    public function getUser(int $laravelUserId): ?array
    {
        $docId = $this->getUserDocId($laravelUserId);
        return $this->client->getDoc($this->collection, $docId);
    }

    /**
     * Lista todos los usuarios en Firestore.
     */
    public function list(): array
    {
        return $this->client->listDocs($this->collection);
    }

    /**
     * Elimina un usuario de Firestore.
     */
    public function delete(int $laravelUserId): bool
    {
        $docId = $this->getUserDocId($laravelUserId);
        return $this->client->deleteDoc($this->collection, $docId);
    }

    /**
     * Convierte el ID de Laravel al ID de documento Firestore.
     */
    protected function getUserDocId(int $laravelUserId): string
    {
        return "u_{$laravelUserId}";
    }

    /**
     * Extrae el rol del usuario.
     * Primero intenta usando Spatie (hasRole),
     * luego busca el campo `role` directo en el modelo.
     */
    protected function getRole(User $user): string
    {
        // Si el usuario tiene método hasRole (Spatie)
        if (method_exists($user, 'hasRole') && method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames();
            if ($roles && $roles->first()) {
                return (string) $roles->first();
            }
        }

        // Fallback: campo directo en el modelo
        if (isset($user->role)) {
            return (string) $user->role;
        }

        return 'user';
    }
}
