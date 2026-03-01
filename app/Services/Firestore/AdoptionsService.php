<?php

namespace App\Services\Firestore;

use App\Models\AdoptionPost;
use App\Models\AdoptionRequest;

class AdoptionsService
{
    protected FirestoreRestClient $client;

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    public function syncPost(AdoptionPost $post): string
    {
        $docId = 'p_'.$post->id;
        $docPath = "adoptionPosts/{$docId}";
        $data = [
            'id' => $post->id,
            'petId' => $post->pet_id,
            'createdBy' => $post->created_by,
            'title' => $post->title,
            'description' => $post->description,
            'requirements' => $post->requirements,
            'is_active' => (bool) $post->is_active,
            'created_at' => optional($post->created_at)->toDateTimeString(),
            'updated_at' => optional($post->updated_at)->toDateTimeString(),
        ];

        $exists = $this->client->getDocument($docPath);
        if ($exists) {
            $this->client->patchDocument($docPath, $data);
            return 'updated';
        }

        $this->client->createDocument('adoptionPosts', $docId, $data);
        return 'created';
    }

    public function syncRequest(AdoptionRequest $request): string
    {
        $docId = 'ar_'.$request->id;
        $docPath = "adoptionRequests/{$docId}";
        $data = [
            'id' => $request->id,
            'adoptionPostId' => $request->adoption_post_id,
            'applicantId' => $request->applicant_id,
            'message' => $request->message,
            'status' => $request->status,
            'created_at' => optional($request->created_at)->toDateTimeString(),
            'updated_at' => optional($request->updated_at)->toDateTimeString(),
        ];

        $exists = $this->client->getDocument($docPath);
        if ($exists) {
            $this->client->patchDocument($docPath, $data);
            return 'updated';
        }

        $this->client->createDocument('adoptionRequests', $docId, $data);
        return 'created';
    }
}
