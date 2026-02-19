<?php

namespace App\Services\Firestore;

use App\Models\Appointment;

class AppointmentsService
{
    protected FirestoreRestClient $client;

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    public function syncFromModel(Appointment $appointment): string
    {
        $docId = 'a_'.$appointment->id;
        $docPath = "appointments/{$docId}";
        $data = [
            'id' => $appointment->id,
            'clinicId' => $appointment->clinic_id,
            'medicalServiceId' => $appointment->medical_service_id,
            'petId' => $appointment->pet_id,
            'ownerId' => $appointment->owner_id,
            'vetId' => $appointment->vet_id,
            'scheduled_at' => optional($appointment->scheduled_at)->toDateTimeString(),
            'status' => $appointment->status,
            'notes' => $appointment->notes,
            'created_at' => optional($appointment->created_at)->toDateTimeString(),
            'updated_at' => optional($appointment->updated_at)->toDateTimeString(),
        ];

        $exists = $this->client->getDocument($docPath);
        if ($exists) {
            $this->client->patchDocument($docPath, $data);
            return 'updated';
        }

        $this->client->createDocument('appointments', $docId, $data);
        return 'created';
    }
}
