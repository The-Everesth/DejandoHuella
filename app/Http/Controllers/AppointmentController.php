<?php

namespace App\Http\Controllers;

use App\Services\Firestore\ClinicsFirestoreService;
use App\Services\Firestore\MedicalServicesFirestoreService;
use App\Services\Firestore\PetsFirestoreService;
use App\Services\AppointmentsFirestoreService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function create(
        string $clinic,
        string $service,
        Request $request,
        AppointmentsFirestoreService $firestore,
        ClinicsFirestoreService $clinicsFirestore,
        MedicalServicesFirestoreService $servicesFirestore,
        PetsFirestoreService $petsFirestore
    ) {
        // Cargar clínica y servicio desde Firestore
        $clinicData = $clinicsFirestore->getClinicById($clinic);
        if (!$clinicData) {
            Log::error("[CITAS] Clínica Firestore no encontrada: $clinic");
            abort(404, "Clínica no encontrada en Firestore (ID: $clinic)");
        }
        $allServices = $servicesFirestore->listActiveServices();

        // 1) Intento directo por key (docId / firestore_id)
        $serviceData = $allServices[$service] ?? null;
        $serviceKey = $service;

        // 2) Fallback: buscar por campos internos (id / sqlId / sql_id)
        if (!$serviceData) {
            foreach ($allServices as $key => $srv) {
                $candidates = [
                    (string)($srv['id'] ?? ''),
                    (string)($srv['firestore_id'] ?? ''),
                    (string)($srv['sqlId'] ?? ''),
                    (string)($srv['sql_id'] ?? ''),
                    (string)($srv['legacyId'] ?? ''),
                ];

                if (in_array((string)$service, $candidates, true)) {
                    $serviceData = $srv;
                    $serviceKey = $key; // este es el id real que usa el array
                    break;
                }
            }
        }

        if (!$serviceData) {
            Log::error("[CITAS] Servicio no encontrado. Param={$service}. Keys=" . implode(',', array_keys($allServices)));
            abort(404, "Servicio no encontrado (param: $service)");
        }

        // OJO: a partir de aquí usa $serviceKey como el ID del servicio (firestore)
        $service = $serviceKey;

        $user = auth()->user();
        $pets = collect($petsFirestore->listByOwner($user->id))->sortBy('name')->values();

        // Horario y duración
        $clinicSchedule = [
            'start' => $clinicData['schedule_start'] ?? '09:00',
            'end' => $clinicData['schedule_end'] ?? '18:00',
        ];
        $serviceDuration = $serviceData['duration_minutes'] ?? 30;
        $date = $request->input('date', now()->format('Y-m-d'));
        $slots = $firestore->getAvailableSlots($clinic, $date, $serviceDuration, $clinicSchedule);

        // MVP: obtener servicios de la clínica
        $serviceIds = $clinicData['serviceIds'] ?? [];
        $clinicServices = [];
        foreach ($serviceIds as $srvId) {
            if (isset($allServices[$srvId])) {
                $clinicServices[] = $allServices[$srvId];
            }
        }

        return view('appointments.create', [
            'clinic' => (object)$clinicData,
            'service' => (object)$serviceData,
            'pets' => $pets,
            'slots' => $slots,
            'date' => $date,
            'clinicServices' => $clinicServices,
        ]);
    }

    public function store(Request $request, AppointmentsFirestoreService $firestore)
    {
        /** @var User $user */
        $user = auth()->user();


        $data = $request->validate([
            'clinic_id' => 'required',
            'medical_service_id' => 'required',
            'pet_id' => 'required',
            'start_at' => 'required|date_format:Y-m-d H:i',
            'notes' => 'nullable|string|max:1000',
            'contact' => 'nullable|string|max:255',
            'extra_services' => 'array',
        ]);

        // Validar mascota por Firestore
        /** @var \App\Services\Firestore\PetsFirestoreService $petsFirestore */
        $petsFirestore = app(\App\Services\Firestore\PetsFirestoreService::class);
        $pet = $petsFirestore->getById($data['pet_id']);
        $petOk = $pet && ($pet['ownerUid'] ?? null) == $user->id && ($pet['isActive'] ?? false);
        abort_unless($petOk, 403);

        // Firestore: validar clínica y servicio
        $clinicsFirestore = app(\App\Services\Firestore\ClinicsFirestoreService::class);
        $servicesFirestore = app(\App\Services\Firestore\MedicalServicesFirestoreService::class);
        $clinicData = $clinicsFirestore->getClinicById($data['clinic_id']);
        $serviceData = $servicesFirestore->listActiveServices()[$data['medical_service_id']] ?? null;
        abort_unless($clinicData && $serviceData, 404);
        $serviceIds = $clinicData['serviceIds'] ?? [];
        abort_unless(in_array($data['medical_service_id'], $serviceIds, true), 403);

        $extraServices = array_filter(array_map('strval', $data['extra_services'] ?? []));
        // Obtener vetUid (dueño de la clínica)
        $vetUid = $clinicData['ownerUid'] ?? $clinicData['userId'] ?? null;
        $payload = [
            'clinicId' => $data['clinic_id'],
            'serviceId' => $data['medical_service_id'],
            'userUid' => (string)($user->uid ?? $user->id),
            'vetUid' => (string)($vetUid ?? ''),
            'petId' => $data['pet_id'],
            'contact' => $data['contact'] ?? $user->email,
            'notes' => $data['notes'] ?? '',
            'extraServicesJson' => json_encode(array_values($extraServices)),
            'startAt' => $data['start_at'],
            'duration' => $serviceData['duration_minutes'] ?? 30,
            'status' => 'PENDING',
        ];
        try {
            $firestore->createAppointment($payload);
            return redirect()->route('my.appointments')->with('success', 'Cita solicitada. Espera confirmación del veterinario.');
        } catch (\Exception $e) {
            Log::error('Error creando cita: '.$e->getMessage());
            return back()->withInput()->withErrors(['start_at' => $e->getMessage()]);
        }
    }

    // Ciudadano: mis citas
    public function myAppointments(AppointmentsFirestoreService $firestore)
    {
        /** @var User $user */
        $user = auth()->user();
        $userUid = (string)($user->uid ?? $user->id ?? '');
        $appointments = $firestore->listByUser($userUid)->map(function($doc) {
            $a = $doc;
            $a['id'] = $doc['id'] ?? null;
            return (object)$a;
        });
        return view('appointments.my', compact('appointments'));
    }

    // Vet: citas de mis clínicas
    public function vetAppointments(AppointmentsFirestoreService $firestore)
    {
        /** @var User $user */
        $user = auth()->user();
        $vetUid = (string)($user->uid ?? $user->id);
        $appointments = $firestore->listByVet($vetUid)->map(function($a) {
            return (object)$a;
        });
        return view('vet.appointments.index', compact('appointments'));
    }

    // Vet/Admin: cambiar estado
    public function setStatus(Request $request, $appointmentId, AppointmentsFirestoreService $firestore)
    {
        $data = $request->validate([
            'status' => 'required|in:CONFIRMED,REJECTED',
            'vetNotes' => 'nullable|string|max:1000',
        ]);
        try {
            $extra = [];
            if (isset($data['vetNotes'])) {
                $extra['vetNotes'] = $data['vetNotes'];
            }
            $firestore->setStatus($appointmentId, $data['status'], $extra);
            $msg = $data['status'] === 'CONFIRMED' ? 'Cita confirmada.' : 'Cita rechazada.';
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }
}
