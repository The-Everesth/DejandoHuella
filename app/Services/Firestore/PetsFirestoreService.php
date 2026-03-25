<?php

namespace App\Services\Firestore;

use Illuminate\Support\Facades\Log;

class PetsFirestoreService
{
    protected FirestoreRestClient $client;
    protected string $collection = 'pets';

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    /**
     * Lista todas las mascotas del sistema
     */
    public function listAll(): array
    {
        return $this->client->listDocs($this->collection);
    }

    /**
     * Lista mascotas activas por dueño (ownerUid)
     */

    public function listByOwner(string $ownerUid): array
    {
        $all = $this->client->listDocs($this->collection);
        Log::info('[PETS] filter', ['ownerUid' => $ownerUid, 'count_all' => count($all)]);
        $results = [];
        foreach ($all as $docId => $doc) {
            if (
                isset($doc['ownerUid'], $doc['isActive']) &&
                (string)$doc['ownerUid'] === (string)$ownerUid &&
                (bool)$doc['isActive'] === true
            ) {
                $doc['id'] = $docId;
                $results[] = $doc;
            }
        }
        return $results;
    }


    /**
     * Obtiene mascota por ID
     */
    public function getById(string $petId): ?array
    {
        return $this->client->getDoc($this->collection, $petId);
    }

    /**
     * Crea mascota (ownerUid requerido)
     */
    public function createPet(array $data): array
    {
        if (empty($data['ownerUid'])) {
            throw new \InvalidArgumentException('ownerUid requerido');
        }

        $now = now()->toIso8601String();
        $data['isActive'] = true;
        $data['createdAt'] = $now;
        $data['updatedAt'] = $now;

        Log::info('[PETS] create payload', $data);

        try {
            // createDoc permite id null -> Firestore asigna uno automáticamente (según tu client).
            $result = $this->client->createDoc($this->collection, null, $data);

            // Algunos clients regresan el doc completo, otros solo metadata.
            // Intentamos obtener el id en distintas formas.
            $createdId = $result['id'] ?? $result['name'] ?? null;

            // Si viene como path "pets/{id}", extraemos el último segmento
            if (is_string($createdId) && str_contains($createdId, '/')) {
                $createdId = basename($createdId);
            }

            Log::info('[PETS] created', ['id' => $createdId]);

            // Si no pudimos obtener el id, hacemos fallback: buscar la más reciente del owner
            if (!$createdId) {
                $all = $this->listByOwner((string)$data['ownerUid']);
                $created = collect($all)->sortByDesc('createdAt')->first();
                $createdId = $created['id'] ?? null;
            }

            return $createdId ? ($this->getById((string)$createdId) ?? []) : [];
        } catch (\Throwable $e) {
            Log::error('[PETS] error al crear', ['msg' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Actualiza mascota
     */
    public function updatePet(string $petId, array $fields): void
    {
        $fields['updatedAt'] = now();
        // Solo permitir actualizar campos válidos
        $allowed = ['photoUrl', 'photoPath', 'updatedAt', 'name', 'age', 'breed', 'description', 'type', 'sex', 'ownerUid'];
        $filtered = array_intersect_key($fields, array_flip($allowed));
        \Log::info('[PET] updatePet', ['petId' => $petId, 'fields' => $filtered]);
        $this->client->patchDoc($this->collection, $petId, $filtered);
    }

    /**
     * Soft delete mascota (isActive=false)
     */
    public function deletePet(string $petId): bool
    {
        return (bool)$this->client->patchDoc($this->collection, $petId, [
            'isActive' => false,
            'updatedAt' => now()->toIso8601String(),
        ]);
    }
}