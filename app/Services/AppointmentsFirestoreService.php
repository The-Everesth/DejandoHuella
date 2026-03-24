<?php

namespace App\Services;

use App\Services\Firestore\FirestoreRestClient;
use Illuminate\Support\Facades\Log;
use Exception;

class AppointmentsFirestoreService
{
    /**
     * Obtiene una cita por su ID
     */
    public function getById(string $appointmentId)
    {
        $doc = $this->client->getDoc($this->collection, $appointmentId);
        if ($doc) {
            $doc['id'] = $appointmentId;
        }
        return $doc;
    }
    protected FirestoreRestClient $client;
    protected string $collection = 'appointments';

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    /**
     * Lista citas por múltiples clínicas y estados
     * @param array $clinicIds
     * @param array $statuses
     * @return array
     */
    public function listByClinics(array $clinicIds, array $statuses = ['PENDING','CONFIRMED']): array
    {
        $all = $this->client->listDocs($this->collection);
        $results = [];
        foreach ($all as $docId => $doc) {
            if (
                isset($doc['clinicId'], $doc['status']) &&
                in_array($doc['clinicId'], $clinicIds) &&
                in_array($doc['status'], $statuses)
            ) {
                $doc['id'] = $docId;
                $results[] = $doc;
            }
        }
        // Opcional: ordenar por startAt descendente
        usort($results, function($a, $b) {
            return strcmp($b['startAt'] ?? '', $a['startAt'] ?? '');
        });
        return $results;
    }

    /**
     * Crea una cita en Firestore, validando anti doble-reserva.
     * @throws Exception
     */
    public function createAppointment(array $payload)
    {
        $clinicId = $payload['clinicId'] ?? null;
        $startAt = $payload['startAt'] ?? null;
        if (!$clinicId || !$startAt) {
            throw new Exception('Datos insuficientes para crear cita.');
        }
        // Verificar doble-reserva
        // Buscar si existe cita activa en ese horario
        if ($this->existsActiveAtSlot($clinicId, $startAt)) {
            throw new Exception('Horario no disponible');
        }
        $payload['status'] = 'PENDING';
        $payload['createdAt'] = now()->toIso8601String();
        $payload['updatedAt'] = now()->toIso8601String();
        // MVP: serializar extraServices como JSON string
        if (isset($payload['extraServices'])) {
            $payload['extraServicesJson'] = json_encode($payload['extraServices']);
            unset($payload['extraServices']);
        }
        // Forzar userUid y vetUid como string
        if (isset($payload['userUid'])) {
            $payload['userUid'] = (string)$payload['userUid'];
        }
        if (isset($payload['vetUid'])) {
            $payload['vetUid'] = (string)$payload['vetUid'];
        }
        Log::info('[APPT] payload raw', $payload);
        $id = 'appt_' . uniqid();
        $this->client->createDoc($this->collection, $id, $payload);
        return $id;
    }

    /**
     * Lista citas por usuario
     */
    public function listByUser(string $userUid)
    {
        $all = $this->client->listDocs($this->collection);
        $results = [];
        foreach ($all as $docId => $doc) {
            if (isset($doc['userUid']) && $doc['userUid'] === $userUid) {
                $doc['id'] = $docId;
                $results[] = $doc;
            }
        }
        usort($results, function($a, $b) {
            return strcmp($b['startAt'] ?? '', $a['startAt'] ?? '');
        });
        return collect($results);
    }

    /**
     * Lista citas por veterinario
     */
    public function listByVet(string $vetUid)
    {
        $all = $this->client->listDocs($this->collection);
        $results = [];
        foreach ($all as $docId => $doc) {
            if (isset($doc['vetUid']) && $doc['vetUid'] === $vetUid) {
                $doc['id'] = $docId;
                $results[] = $doc;
            }
        }
        usort($results, function($a, $b) {
            return strcmp($b['startAt'] ?? '', $a['startAt'] ?? '');
        });
        return collect($results);
    }

    /**
     * Cambia el status de una cita
     */
    public function setStatus(string $appointmentId, string $status, array $extraData = [])
    {
        $data = array_merge(['status' => $status, 'updatedAt' => now()->toIso8601String()], $extraData);
        $this->client->patchDoc($this->collection, $appointmentId, $data);
    }

    /**
     * Genera slots disponibles para una clínica y fecha
     * @return array
     */
    public function getAvailableSlots(string $clinicId, string $dateYmd, int $serviceDuration, array $clinicSchedule)
    {
        // $clinicSchedule: ['start' => '09:00', 'end' => '18:00']
        $slots = [];
        $start = strtotime("$dateYmd " . $clinicSchedule['start']);
        $end = strtotime("$dateYmd " . $clinicSchedule['end']);
        for ($t = $start; $t + $serviceDuration * 60 <= $end; $t += $serviceDuration * 60) {
            $slots[] = date('H:i', $t);
        }
        // Filtrar ocupados
        $busy = $this->listActiveByClinicAndDate($clinicId, $dateYmd);
        $busyTimes = [];
        foreach ($busy as $doc) {
            $busyTimes[] = substr($doc['startAt'], 11, 5);
        }
        return array_values(array_diff($slots, $busyTimes));
    }
    /**
     * Lista citas activas (PENDING/CONFIRMED) por clínica y fecha (Y-m-d)
     */
    private function listActiveByClinicAndDate(string $clinicId, string $dateYmd): array
    {
        $all = $this->client->listDocs($this->collection);
        $results = [];
        foreach ($all as $docId => $doc) {
            if (
                isset($doc['clinicId'], $doc['status'], $doc['startAt']) &&
                $doc['clinicId'] === $clinicId &&
                in_array($doc['status'], ['PENDING', 'CONFIRMED']) &&
                strpos($doc['startAt'], $dateYmd) === 0
            ) {
                $doc['id'] = $docId;
                $results[] = $doc;
            }
        }
        return $results;
    }

    /**
     * Verifica si existe cita activa en el mismo slot (misma clínica y startAt)
     */
    private function existsActiveAtSlot(string $clinicId, string $startAtIso): bool
    {
        $all = $this->client->listDocs($this->collection);
        foreach ($all as $doc) {
            if (
                isset($doc['clinicId'], $doc['status'], $doc['startAt']) &&
                $doc['clinicId'] === $clinicId &&
                $doc['startAt'] === $startAtIso &&
                in_array($doc['status'], ['PENDING', 'CONFIRMED'])
            ) {
                return true;
            }
        }
        return false;
    }
}