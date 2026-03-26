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
     * Actualiza campos específicos del usuario en Firestore (PATCH parcial).
     */
    public function updateUserFields(string $docId, array $fields): bool
    {
        try {
            $this->client->patchDoc($this->collection, $docId, $fields);
            return true;
        } catch (\Throwable $e) {
            Log::error('Error actualizando campos de usuario en Firestore', [
                'docId' => $docId,
                'fields' => $fields,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
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
                'profilePhotoUrl'  => $user->profile_photo_url,
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
     * Obtiene un usuario desde Firestore por docId (ID real de Firestore).
     */
    public function getUserByDocId(string $docId): ?array
    {
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
    /**
     * Devuelve el docId real de Firestore para un usuario.
     * Si el ID ya es string (autogenerado por Firestore), lo retorna tal cual.
     * Si es numérico, retorna u_# para compatibilidad.
     */
    public function getUserDocId($userId): string
    {
        if (is_string($userId) && !preg_match('/^\d+$/', $userId)) {
            // ID Firestore autogenerado (alfanumérico largo)
            return $userId;
        }
        // Si es numérico, usar formato u_#
        return "u_{$userId}";
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
