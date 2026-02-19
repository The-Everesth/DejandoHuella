<?php

namespace App\Services\Firestore;

use App\Models\Clinic;

class ClinicsService
{
    protected FirestoreRestClient $client;

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    /**
     * Synchronize a single Clinic model to Firestore.
     * Returns 'created' or 'updated'.
     */
    public function syncFromModel(Clinic $clinic): string
    {
        $docId = 'c_'.$clinic->id;
        $docPath = "clinics/{$docId}";

        $data = [
            'id' => $clinic->id,
            'userId' => $clinic->user_id,
            'name' => $clinic->name,
            'phone' => $clinic->phone,
            'email' => $clinic->email,
            'address' => $clinic->address,
            'description' => $clinic->description,
            'opening_hours' => $clinic->opening_hours,
            'website' => $clinic->website,
            'is_public' => (bool) $clinic->is_public,
            'created_at' => optional($clinic->created_at)->toDateTimeString(),
            'updated_at' => optional($clinic->updated_at)->toDateTimeString(),
        ];

        $exists = $this->client->getDocument($docPath);
        if ($exists) {
            $this->client->patchDocument($docPath, $data);
            return 'updated';
        }

        $this->client->createDocument('clinics', $docId, $data);
        return 'created';
    }
}
