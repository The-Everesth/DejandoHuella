<?php

namespace App\Services\Firestore;

use App\Models\User;

class UsersMirrorService
{
    protected $client;

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    public function syncFromModel(User $user): string
    {
        $docId = 'u_'.$user->id;
        $docPath = "users/{$docId}";
        $data = [
            'laravelUserId' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first() ?: 'ciudadano',
            'status' => $user->deleted_at ? 'inactive' : 'active',
            'updatedAt' => now()->toIso8601String(),
        ];

        $exists = $this->client->getDocument($docPath);
        if ($exists) {
            $this->client->patchDocument($docPath, $data);
            return 'updated';
        }

        $this->client->createDocument('users', $docId, $data);
        return 'created';
    }
}
