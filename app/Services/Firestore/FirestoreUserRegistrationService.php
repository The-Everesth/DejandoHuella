<?php

namespace App\Services\Firestore;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FirestoreUserRegistrationService
{
    protected $client;
    protected $collection = 'users';

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    /**
     * Registra un usuario en Firestore con hash bcrypt de la contraseña.
     */
    /**
     * Crea un usuario nuevo en Firestore con ID autogenerado.
     */
    public function create(array $data): array
    {
        $now = now()->toIso8601String();
        // Obtener el siguiente user_code disponible
        $nextUserCode = $this->getNextUserCode();
        $payload = [
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'ciudadano',
            'status' => 'active',
            'createdAt' => $now,
            'updatedAt' => $now,
            'user_code' => $nextUserCode,
        ];
        // Usar createDoc sin ID para que Firestore genere uno único
        $doc = $this->client->createDoc($this->collection, null, $payload);
        // El ID generado por Firestore
        $payload['id'] = $doc['id'] ?? ($doc['name'] ? basename($doc['name']) : null);
        return $payload;
    }

    /**
     * Busca el mayor user_code existente y retorna el siguiente correlativo disponible (u_#).
     */
    protected function getNextUserCode(): string
    {
        $users = $this->client->listDocs($this->collection);
        $max = 0;
        foreach ($users as $user) {
            if (isset($user['user_code']) && preg_match('/^u_(\d+)$/', $user['user_code'], $m)) {
                $num = (int)$m[1];
                if ($num > $max) {
                    $max = $num;
                }
            }
            // Compatibilidad: si el docId es tipo u_# y no tiene user_code
            if (isset($user['id']) && preg_match('/^u_(\d+)$/', $user['id'], $m)) {
                $num = (int)$m[1];
                if ($num > $max) {
                    $max = $num;
                }
            }
        }
        return 'u_' . ($max + 1);
    }

    /**
     * Actualiza un usuario existente en Firestore (requiere ID).
     */
    public function update(string $id, array $data): array
    {
        $now = now()->toIso8601String();
        $payload = [
            'name' => $data['name'] ?? null,
            'email' => isset($data['email']) ? strtolower(trim($data['email'])) : null,
            'role' => $data['role'] ?? null,
            'status' => $data['status'] ?? null,
            'updatedAt' => $now,
        ];
        // Limpiar nulos
        $payload = array_filter($payload, fn($v) => !is_null($v));
        $this->client->patchDoc($this->collection, $id, $payload);
        $payload['id'] = $id;
        return $payload;
    }

    // Eliminado: getDocIdFromEmail. Ahora los IDs son incrementales u_N
}
