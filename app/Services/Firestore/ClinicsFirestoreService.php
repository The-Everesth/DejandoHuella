<?php
namespace App\Services\Firestore;

use Illuminate\Support\Facades\Log;

class ClinicsFirestoreService
{
    protected FirestoreRestClient $client;
    protected string $collection = 'clinicas';

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create or update the clinic for a veterinarian (one clinic per vet).
     * The document id will be deterministic: clinic_u_{userId}
     */
    public function createOrUpdateClinicForVet(int $userId, array $data): array
    {
        $id = 'clinic_u_'.$userId;
        $data['ownerUserId'] = $userId;
        $data['updatedAt'] = now()->toIso8601String();
        if (! isset($data['createdAt'])) {
            $data['createdAt'] = now()->toIso8601String();
        }
        
        Log::info('ClinicsFirestoreService::createOrUpdateClinicForVet() - Creating/updating clinic', [
            'id' => $id,
            'userId' => $userId,
            'dataKeys' => array_keys($data)
        ]);
        
        $this->client->createDoc($this->collection, $id, $data);
        
        Log::info('ClinicsFirestoreService::createOrUpdateClinicForVet() - createDoc completed, fetching document');
        
        $result = $this->client->getDoc($this->collection, $id);
        if (!$result) {
            Log::warning('ClinicsFirestoreService::createOrUpdateClinicForVet() - getDoc returned null after create, returning submitted data');
            return $data;
        }
        
        Log::info('ClinicsFirestoreService::createOrUpdateClinicForVet() - Document fetched successfully', [
            'resultKeys' => array_keys($result)
        ]);
        
        return $result;
    }

    public function getClinicById(string $clinicId): ?array
    {
        return $this->client->getDoc($this->collection, $clinicId);
    }

    public function getClinicByOwnerUserId(int $userId): ?array
    {
        $id = 'clinic_u_'.$userId;
        return $this->client->getDoc($this->collection, $id);
    }

    /**
     * Lista todos los documentos de clínicas (sin filtrar).
     * Útil para API/depuración.
     */
    public function list(): array
    {
        return $this->client->listDocs($this->collection);
    }

    /**
     * List published clinics. Filters: serviceId, q (search by name)
     */
    public function listPublishedClinics(array $filters = []): array
    {
        $all = $this->client->listDocs($this->collection);
        $out = [];
        $serviceId = $filters['serviceId'] ?? null;
        $q = isset($filters['q']) ? mb_strtolower($filters['q']) : null;

        foreach ($all as $clinic) {
            if (isset($clinic['published']) && $clinic['published'] === true) {
                if ($serviceId) {
                    $services = $clinic['serviceIds'] ?? ($clinic['services'] ?? []);
                    if (! is_array($services) || ! in_array($serviceId, $services, true)) {
                        continue;
                    }
                }
                if ($q) {
                    $name = mb_strtolower($clinic['name'] ?? '');
                    $address = mb_strtolower(($clinic['address'] ?? '').' '.($clinic['address_line'] ?? ''));
                    if (strpos($name, $q) === false && strpos($address, $q) === false) {
                        continue;
                    }
                }
                $out[$clinic['id']] = $clinic;
            }
        }
        return $out;
    }

    /**
     * Sync Eloquent Clinic model to Firestore (dual-write from MySQL)
     * Document ID format: c_{clinicId}
     * Uses PATCH for updates if document exists, CREATE if new
     */
    public function syncFromModel(\App\Models\Clinic $clinic): array
    {
        $id = 'c_' . $clinic->id;
        
        $data = [
            'id' => $id,
            'name' => $clinic->name,
            'phone' => $clinic->phone,
            'email' => $clinic->email,
            'address' => $clinic->address,
            'description' => $clinic->description,
            'opening_hours' => $clinic->opening_hours,
            'website' => $clinic->website,
            'is_public' => (bool)$clinic->is_public,
            'userId' => $clinic->user_id,
            'createdAt' => $clinic->created_at?->toIso8601String(),
            'updatedAt' => now()->toIso8601String(),
        ];
        
        Log::info('ClinicsFirestoreService::syncFromModel() - Starting sync to Firestore', [
            'id' => $id,
            'clinicId' => $clinic->id,
            'userId' => $clinic->user_id,
            'fields' => array_keys($data),
            'isPublic' => $clinic->is_public,
            'dataSnapshot' => [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
            ],
        ]);
        
        try {
            Log::info('ClinicsFirestoreService::syncFromModel() - About to call patchDoc', [
                'id' => $id,
                'collection' => $this->collection,
            ]);
            
            // Use patchDoc directly so it handles create/update automatically
            $syncResult = $this->client->patchDoc($this->collection, $id, $data);
            Log::info('ClinicsFirestoreService::syncFromModel() - patchDoc returned, verifying...', [
                'id' => $id,
                'patchResult' => $syncResult,
            ]);
            
            // Verify the document was actually written
            $verified = $this->client->getDoc($this->collection, $id);
            if (!$verified) {
                Log::warning('ClinicsFirestoreService::syncFromModel() - Verification FAILED: getDoc returned null', [
                    'id' => $id,
                    'clinicId' => $clinic->id,
                ]);
                return $data;
            }
            
            Log::info('ClinicsFirestoreService::syncFromModel() - Verification SUCCESS: Data matched', [
                'id' => $id,
                'clinicId' => $clinic->id,
                'verifiedData' => [
                    'fsName' => $verified['name'] ?? null,
                    'fsPhone' => $verified['phone'] ?? null,
                    'fsAddress' => $verified['address'] ?? null,
                    'matchName' => ($verified['name'] ?? null) === $clinic->name,
                    'matchPhone' => ($verified['phone'] ?? null) === $clinic->phone,
                ],
            ]);
            return $verified;
        } catch (\Throwable $e) {
            Log::error('ClinicsFirestoreService::syncFromModel() - EXCEPTION during sync', [
                'id' => $id,
                'clinicId' => $clinic->id,
                'error' => $e->getMessage(),
                'errorCode' => $e->getCode(),
                'errorClass' => class_basename($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't throw, allow MySQL write to succeed
            return $data;
        }
    }

    /**
     * Update the serviceIds array for a clinic document.
     */
    public function updateClinicServices(string $clinicId, array $serviceIds): bool
    {
        $result = $this->client->patchDoc($this->collection, $clinicId, [
            'serviceIds' => $serviceIds,
            'updatedAt' => now()->toIso8601String(),
        ]);
        return (bool)$result;
    }

    /**
     * Delete clinic from Firestore by Eloquent model ID
     */
    public function deleteFromModel(\App\Models\Clinic $clinic): bool
    {
        $id = 'c_' . $clinic->id;
        
        Log::info('ClinicsFirestoreService::deleteFromModel() - Deleting clinic from Firestore', [
            'id' => $id,
            'clinicId' => $clinic->id,
        ]);
        
        try {
            $exists = $this->client->getDoc($this->collection, $id);
            if (!$exists) {
                Log::info('ClinicsFirestoreService::deleteFromModel() - Clinic not found in Firestore, skipping delete', ['id' => $id]);
                return true;
            }
            $this->client->deleteDoc($this->collection, $id);
            Log::info('ClinicsFirestoreService::deleteFromModel() - Clinic deleted from Firestore', ['id' => $id]);
            return true;
        } catch (\Throwable $e) {
            Log::error('ClinicsFirestoreService::deleteFromModel() - Error deleting clinic from Firestore', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw, allow MySQL delete to succeed
            return false;
        }
    }

    /**
     * Delete clinic only if owner matches
     */
    public function deleteClinic(string $clinicId, int $ownerUserId): bool
    {
        $clinic = $this->getClinicById($clinicId);
        if (! $clinic) {
            return false;
        }
        if (! isset($clinic['ownerUserId']) || (int)$clinic['ownerUserId'] !== $ownerUserId) {
            throw new \RuntimeException('No autorizado para eliminar esta clínica');
        }
        return $this->client->deleteDoc($this->collection, $clinicId);
    }
}