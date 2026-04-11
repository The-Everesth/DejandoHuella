<?php
namespace App\Services\Firestore;

class MedicalServicesFirestoreService
{
    protected FirestoreRestClient $client;
    protected string $collection = 'servicios_medicos';

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    public function listActiveServices(): array
    {
        $all = $this->client->listDocs($this->collection);
        if (count($all) === 0) {
            $this->seedIfEmpty([
                [
                    'id' => 'srv_consulta',
                    'name' => 'Consulta general',
                    'durationMinutes' => 30,
                    'is_active' => true,
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => now()->toIso8601String(),
                ],
                [
                    'id' => 'srv_vacuna',
                    'name' => 'Vacunación',
                    'durationMinutes' => 20,
                    'is_active' => true,
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => now()->toIso8601String(),
                ],
                [
                    'id' => 'srv_desparasitacion',
                    'name' => 'Desparasitación',
                    'durationMinutes' => 20,
                    'is_active' => true,
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => now()->toIso8601String(),
                ],
                [
                    'id' => 'srv_urgencia',
                    'name' => 'Urgencia',
                    'durationMinutes' => 45,
                    'is_active' => true,
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => now()->toIso8601String(),
                ],
                [
                    'id' => 'srv_esterilizacion',
                    'name' => 'Esterilización',
                    'durationMinutes' => 60,
                    'is_active' => true,
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => now()->toIso8601String(),
                ],
            ]);
            $all = $this->client->listDocs($this->collection);
        }
        $out = [];
        foreach ($all as $s) {
            if (! isset($s['is_active']) || $s['is_active'] === true) {
                $out[$s['id']] = $s;
            }
        }
        return $out;
    }

    /**
     * Optional seeder: create basic services if none exist
     */
    public function seedIfEmpty(array $defaults = []): int
    {
        $all = $this->client->listDocs($this->collection);
        $existingIds = array_map(fn($s) => $s['id'] ?? null, $all);
        $created = 0;
        foreach ($defaults as $d) {
            $id = $d['id'] ?? null;
            if (!$id || in_array($id, $existingIds, true)) continue;
            $this->client->createDoc($this->collection, $id, $d);
            $created++;
        }
        return $created;
    }
    public function createService(array $data): array
    {
        return $this->client->createDoc($this->collection, null, $data);
    }

    public function updateService(string $id, array $data): array
    {
        return $this->client->patchDoc($this->collection, $id, $data);
    }

    public function deleteService(string $id): void
    {
        $this->client->deleteDoc($this->collection, $id);
    }
}
