<?php

namespace App\Services\Firestore;

use Illuminate\Support\Str;

class FirestoreSupportTicketService
{
    protected $collection = 'support_tickets';
    protected $client;

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    public function listByUser($userId)
    {
        $all = $this->client->listDocs($this->collection);
        $results = [];
        foreach ($all as $docId => $doc) {
            if (($doc['user_id'] ?? null) === $userId) {
                if (empty($doc['id'])) {
                    $doc['id'] = $docId;
                }
                $results[] = $doc;
            }
        }
        usort($results, function($a, $b) {
            return strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? ''));
        });
        return $results;
    }

    public function create($userId, array $data)
    {
        $id = 'ticket_' . Str::random(16);
        $payload = array_merge($data, [
            'id' => $id,
            'user_id' => $userId,
            'created_at' => now()->toIso8601String(),
            'status' => $data['status'] ?? 'pendiente',
        ]);
        $this->client->createDocument($this->collection, $id, $payload);
        return $payload;
    }
}
