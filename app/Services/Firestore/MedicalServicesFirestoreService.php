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
        $out = [];
        foreach ($all as $s) {
            if (! isset($s['active']) || $s['active'] === true) {
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
        if (count($all) > 0) {
            return 0;
        }
        $created = 0;
        foreach ($defaults as $d) {
            $id = $d['id'] ?? null;
            $this->client->createDoc($this->collection, $id, $d);
            $created++;
        }
        return $created;
    }
}
