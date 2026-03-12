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

    public function createForAdoption(string $adoptionId, int $applicantId, array $data): array
    {
        $requestId = $this->buildRequestId($adoptionId, $applicantId);

        $existing = $this->client->getDoc($this->collection, $requestId);
        if (is_array($existing)) {
            throw new \RuntimeException('Ya enviaste una solicitud para esta mascota.');
        }

        $payload = array_merge($data, [
            'id' => $requestId,
            'adoptionId' => $adoptionId,
            'applicantId' => $applicantId,
            'status' => $data['status'] ?? 'pendiente',
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
     * Lista todas las solicitudes de adopcion realizadas por un usuario.
     */
    public function listByApplicant(int $applicantId): array
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

        if (empty($normalizedIds)) {
            return [];
        }

        $lookup = array_fill_keys($normalizedIds, true);
        $all = $this->client->listDocs($this->collection);
        $results = [];

        foreach ($all as $docId => $doc) {
            $adoptionId = (string) ($doc['adoptionId'] ?? '');
            if ($adoptionId === '' || ! isset($lookup[$adoptionId])) {
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

    protected function buildRequestId(string $adoptionId, int $applicantId): string
    {
        $safeAdoptionId = preg_replace('/[^A-Za-z0-9_-]/', '_', $adoptionId) ?: 'adopcion';

        return 'req_'.$safeAdoptionId.'_u_'.$applicantId;
    }
}
