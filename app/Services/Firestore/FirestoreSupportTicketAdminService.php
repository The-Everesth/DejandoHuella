<?php

namespace App\Services\Firestore;

use Illuminate\Support\Str;

class FirestoreSupportTicketAdminService{
    protected $collection = 'support_tickets';
    protected $client;

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }



    public function update($id, array $data)
    {
        return $this->client->patchDoc($this->collection, $id, $data);
    }


    public function listAll($filters = [])
    {
        $all = $this->client->listDocs($this->collection);
        $results = [];
        foreach ($all as $docId => $doc) {
            if (empty($doc['id'])) {
                $doc['id'] = $docId;
            }
            // Filtros
            if (isset($filters['status']) && $filters['status'] !== 'all' && ($doc['status'] ?? null) !== $filters['status']) {
                continue;
            }
            if (isset($filters['priority']) && $filters['priority'] && ($doc['priority'] ?? null) !== $filters['priority']) {
                continue;
            }
            if (isset($filters['q']) && $filters['q'] !== '') {
                $q = mb_strtolower($filters['q']);
                $subject = mb_strtolower($doc['subject'] ?? '');
                $message = mb_strtolower($doc['message'] ?? '');
                if (strpos($subject, $q) === false && strpos($message, $q) === false) {
                    continue;
                }
            }
            $results[] = $doc;
        }
        usort($results, function($a, $b) {
            return strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? ''));
        });
        return $results;
    }

    public function countByStatus($status)
    {
        $all = $this->client->listDocs($this->collection);
        $count = 0;
        foreach ($all as $doc) {
            if (($doc['status'] ?? null) === $status) {
                $count++;
            }
        }
        return $count;
    }
}
