<?php

namespace App\Services\Firestore;

use App\Models\SupportTicket;

class SupportTicketsService
{
    protected FirestoreRestClient $client;

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    public function syncFromModel(SupportTicket $ticket): string
    {
        $docId = 't_'.$ticket->id;
        $docPath = "supportTickets/{$docId}";
        $data = [
            'id' => $ticket->id,
            'userId' => $ticket->user_id,
            'subject' => $ticket->subject,
            'priority' => $ticket->priority,
            'message' => $ticket->message,
            'status' => $ticket->status,
            'seen_at' => optional($ticket->seen_at)->toDateTimeString(),
            'answered_by' => $ticket->answered_by,
            'admin_reply' => $ticket->admin_reply,
            'answered_at' => optional($ticket->answered_at)->toDateTimeString(),
            'created_at' => optional($ticket->created_at)->toDateTimeString(),
            'updated_at' => optional($ticket->updated_at)->toDateTimeString(),
        ];

        $exists = $this->client->getDocument($docPath);
        if ($exists) {
            $this->client->patchDocument($docPath, $data);
            return 'updated';
        }

        $this->client->createDocument('supportTickets', $docId, $data);
        return 'created';
    }
}
