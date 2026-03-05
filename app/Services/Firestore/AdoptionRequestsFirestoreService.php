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

    protected function buildRequestId(string $adoptionId, int $applicantId): string
    {
        $safeAdoptionId = preg_replace('/[^A-Za-z0-9_-]/', '_', $adoptionId) ?: 'adopcion';

        return 'req_'.$safeAdoptionId.'_u_'.$applicantId;
    }
}
