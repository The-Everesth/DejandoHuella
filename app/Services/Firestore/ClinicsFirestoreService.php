<?php

namespace App\Services\Firestore;

class ClinicsFirestoreService
{
    protected FirestoreRestClient $client;
    protected string $collection = 'clinics';

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    public function get(string $id): ?array
    {
        return $this->client->getDoc($this->collection, $id);
    }

    public function list(): array
    {
        return $this->client->listDocs($this->collection);
    }

    public function create(array $data, ?string $id = null): array
    {
        return $this->client->createDoc($this->collection, $id, $data);
    }

    public function update(string $id, array $data): bool
    {
        return $this->client->patchDoc($this->collection, $id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->client->deleteDoc($this->collection, $id);
    }
}
