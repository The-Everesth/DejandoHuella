<?php

namespace App\Services\Firestore;

class AdoptionRequestsFirestoreService
{
    protected FirestoreRestClient $client;
    protected string $collection = 'solicitudes_adopcion';

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    public function createForAdoption(string $adoptionId, $applicantId, array $data): array
    {
        $requestId = $this->buildRequestId($adoptionId, $applicantId);
        $targetStatus = (string) ($data['status'] ?? 'pendiente');

        $existing = $this->client->getDoc($this->collection, $requestId);
        if (is_array($existing)) {
            $existingStatus = strtolower(trim((string) ($existing['status'] ?? 'pendiente')));
            $isCancelled = in_array($existingStatus, ['cancelada', 'cancelled', 'canceled'], true);

            if (! $isCancelled) {
                throw new \RuntimeException('Ya enviaste una solicitud para esta mascota.');
            }

            $payload = array_merge($data, [
                'id' => $requestId,
                'adoptionId' => $adoptionId,
                'applicantId' => $applicantId,
                'status' => $targetStatus,
                'createdAt' => now()->toIso8601String(),
                'updatedAt' => now()->toIso8601String(),
                'cancelledAt' => null,
                'cancelledBy' => null,
                'reviewedAt' => null,
                'reviewedBy' => null,
            ]);

            $this->client->patchDoc($this->collection, $requestId, $payload);

            return $this->client->getDoc($this->collection, $requestId) ?? $payload;
        }

        $payload = array_merge($data, [
            'id' => $requestId,
            'adoptionId' => $adoptionId,
            'applicantId' => $applicantId,
            'status' => $targetStatus,
            'createdAt' => now()->toIso8601String(),
            'updatedAt' => now()->toIso8601String(),
        ]);

        try {
            $this->client->createDocument($this->collection, $requestId, $payload);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 409) {
                throw new \RuntimeException('Ya enviaste una solicitud para esta mascota.');
            }

            throw $e;
        }

        return $this->client->getDoc($this->collection, $requestId) ?? $payload;
    }

    public function get(string $requestId): ?array
    {
        return $this->client->getDoc($this->collection, $requestId);
    }

    /**
     * Lista solicitudes de adopcion asociadas a publicaciones especificas.
     *
     * @param  array<int, string>  $adoptionIds
     */
    public function listByAdoptionIds(array $adoptionIds): array
    {
        $normalizedIds = array_values(array_unique(array_filter(
            array_map(static fn ($id): string => trim((string) $id), $adoptionIds),
            static fn (string $id): bool => $id !== ''
        )));
        $all = $this->client->listDocs($this->collection);
        $results = [];
        foreach ($all as $doc) {
            if (isset($doc['adoptionId']) && in_array((string)$doc['adoptionId'], $normalizedIds, true)) {
                $results[] = $doc;
            }
        }
        $this->sortNewestFirst($results);
        return $results;
    }

    /**
     * Lista todas las solicitudes de adopcion realizadas por un usuario.
     * Permite string o int como ID para soportar Firestore IDs autogenerados.
     */
    public function listByApplicant($applicantId): array
    {

        $all = $this->client->listDocs($this->collection);
        $results = [];
        foreach ($all as $docId => $doc) {
            if ((string) ($doc['applicantId'] ?? '') !== (string) $applicantId) {
                continue;
            }
            if (empty($doc['id'])) {
                $doc['id'] = $docId;
            }
            $results[] = $doc;
        }
        $this->sortNewestFirst($results);
        return $results;
    }

    public function setStatus(string $requestId, string $status, array $extraData = []): bool
    {
        $payload = array_merge([
            'status' => $status,
            'updatedAt' => now()->toIso8601String(),
        ], $extraData);

        return $this->client->patchDoc($this->collection, $requestId, $payload);
    }

    protected function sortNewestFirst(array &$items): void
    {
        usort($items, static function (array $a, array $b): int {
            return strcmp((string) ($b['createdAt'] ?? ''), (string) ($a['createdAt'] ?? ''));
        });
    }

    protected function buildRequestId(string $adoptionId, $applicantId): string
    {
        $safeAdoptionId = preg_replace('/[^A-Za-z0-9_-]/', '_', $adoptionId) ?: 'adopcion';

        return 'req_'.$safeAdoptionId.'_u_'.$applicantId;
    }
}

