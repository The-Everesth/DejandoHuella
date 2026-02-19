<?php

namespace App\Services\Firestore;

use App\Models\Pet;

class PetsService
{
    protected FirestoreRestClient $client;

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    public function syncFromModel(Pet $pet): string
    {
        $docId = 'p_'.$pet->id;
        $docPath = "pets/{$docId}";
        $data = [
            'id' => $pet->id,
            'ownerId' => $pet->owner_id,
            'name' => $pet->name,
            'species' => $pet->species,
            'breed' => $pet->breed,
            'sex' => $pet->sex,
            'birth_date' => optional($pet->birth_date)->toDateString(),
            'color' => $pet->color,
            'is_sterilized' => (bool) $pet->is_sterilized,
            'is_vaccinated' => (bool) $pet->is_vaccinated,
            'description' => $pet->description,
            'photo_path' => $pet->photo_path,
            'created_at' => optional($pet->created_at)->toDateTimeString(),
            'updated_at' => optional($pet->updated_at)->toDateTimeString(),
        ];

        $exists = $this->client->getDocument($docPath);
        if ($exists) {
            $this->client->patchDocument($docPath, $data);
            return 'updated';
        }

        $this->client->createDocument('pets', $docId, $data);
        return 'created';
    }
}
