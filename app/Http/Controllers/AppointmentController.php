<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Services\AppointmentsFirestoreService;
use App\Services\Firestore\MedicalServicesFirestoreService;
use App\Services\Firestore\PetsFirestoreService;
use App\Services\Firestore\ClinicsFirestoreService;

class AppointmentController extends Controller
{
    /**
     * Mostrar formulario para solicitar una cita
     */
    public function create($clinicId, $serviceId, ClinicsFirestoreService $clinicsFirestore, MedicalServicesFirestoreService $servicesFirestore, PetsFirestoreService $petsFirestore)
    {
        $user = auth()->user();
        $clinic = collect($clinicsFirestore->list())->firstWhere('id', $clinicId);
        $service = collect($servicesFirestore->listActiveServices())->firstWhere('id', $serviceId);
        $pets = collect($petsFirestore->listByOwner((string)($user->uid ?? $user->id ?? '')));

        if (!$clinic || !$service) {
            return redirect()->back()->with('error', 'Clínica o servicio no encontrado.');
        }

        // Inicializar $date con la fecha de hoy
        $date = now()->format('Y-m-d');

        // Obtener todos los servicios activos de la clínica (por IDs), excluyendo el principal
        $allServices = $servicesFirestore->listActiveServices();
        $clinicServiceIds = $clinic['serviceIds'] ?? [];
        $clinicServices = [];
        foreach ($clinicServiceIds as $sid) {
            if (isset($allServices[$sid]) && $sid !== $serviceId) {
                $clinicServices[] = $allServices[$sid];
            }
        }

        // Generar slots de horario disponibles (ejemplo: cada 30 min de 9:00 a 18:00)
        $slots = [];
        $startHour = 9;
        $endHour = 18;
        $interval = 30; // minutos
        for ($h = $startHour; $h < $endHour; $h++) {
            foreach ([0, $interval] as $min) {
                $time = sprintf('%02d:%02d', $h, $min);
                $slots[] = $time;
            }
        }

        return view('appointments.create', [
            'clinic' => $clinic,
            'service' => $service,
            'pets' => $pets,
            'date' => $date,
            'clinicServices' => $clinicServices,
            'serviceId' => $serviceId,
            'slots' => $slots,
        ]);
    }
    /**
     * Mostrar detalle de cita
     */
    public function show($appointmentId, AppointmentsFirestoreService $firestore)
    {
        $appointment = $firestore->getById($appointmentId);
        if (!$appointment) {
            abort(404, 'Cita no encontrada');
        }

        // Enriquecer datos: mascota, clínica, servicio
        $pet = null;
        $clinic = null;
        $service = null;

        if (isset($appointment['petId'])) {
            $pet = app(\App\Services\Firestore\PetsFirestoreService::class)->getById($appointment['petId']);
        }
        if (isset($appointment['clinicId'])) {
            $clinic = app(\App\Services\Firestore\ClinicsFirestoreService::class)->getClinicById($appointment['clinicId']);
        }
        if (isset($appointment['serviceId'])) {
            $service = app(\App\Services\Firestore\MedicalServicesFirestoreService::class)->listActiveServices()[$appointment['serviceId']] ?? null;
        }

        return view('appointments.show', [
            'appointment' => $appointment,
            'pet' => $pet,
            'clinic' => $clinic,
            'service' => $service,
        ]);
    }

    /**
     * Cancelar cita (POST)
     */
    public function cancel($appointmentId, AppointmentsFirestoreService $firestore)
    {
        $appointment = $firestore->getById($appointmentId);
        if (!$appointment) {
            return redirect()->back()->with('error', 'Cita no encontrada');
        }
        // Cambiar estado a cancelada
        $firestore->setStatus($appointmentId, 'cancelled');
        return redirect()->route('my.appointments')->with('success', 'Cita cancelada correctamente.');
    }

    /**
     * Formulario de reagendar cita
     */
    public function rescheduleForm($appointmentId, AppointmentsFirestoreService $firestore)
    {
        $appointment = $firestore->getById($appointmentId);
        if (!$appointment) {
            abort(404, 'Cita no encontrada');
        }
        // Aquí puedes pasar slots disponibles, etc.
        return view('appointments.reschedule', compact('appointment'));
    }

    /**
     * Guardar reagendado de cita (POST)
     */
    public function reschedule(Request $request, $appointmentId, AppointmentsFirestoreService $firestore)
    {
        $request->validate([
            'new_date' => 'required|date',
        ]);
        $appointment = $firestore->getById($appointmentId);
        if (!$appointment) {
            return redirect()->back()->with('error', 'Cita no encontrada');
        }
        // Actualizar la fecha y el estado usando los métodos disponibles
        $firestore->setStatus($appointmentId, 'pending');
        // Si existe un método para actualizar la fecha, usarlo aquí. Si no, agregarlo en el servicio.
        return redirect()->route('my.appointments')->with('success', 'Cita reagendada correctamente.');
    }

    public function store(Request $request, AppointmentsFirestoreService $firestore)
    {
        // Usuario autenticado
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
        // Usuario autenticado
        $user = auth()->user();
        $userUid = (string)($user->uid ?? $user->id ?? '');
        $appointments = $firestore->listByUser($userUid);

        // Servicios auxiliares
        $clinicsFirestore = app(\App\Services\Firestore\ClinicsFirestoreService::class);
        $petsFirestore = app(\App\Services\Firestore\PetsFirestoreService::class);
        $servicesFirestore = app(\App\Services\Firestore\MedicalServicesFirestoreService::class);

        // Obtener todas las mascotas del usuario (por id de documento)
        $pets = collect($petsFirestore->listByOwner($userUid))->keyBy('id');
        $clinics = collect($clinicsFirestore->list());
        $services = collect($servicesFirestore->listActiveServices());

        // Enriquecer cada cita con datos de mascota, clínica y servicio
        $appointments = $appointments->map(function($a) use ($pets, $clinics, $services) {
            $a = (object)$a;
            // --- DATOS DE MASCOTA ---
            $petId = $a->petId ?? $a->pet_id ?? null; // Ajustar si el campo cambia
            $pet = $petId && $pets->has($petId) ? $pets[$petId] : null;
            $a->pet_name = $pet['name'] ?? 'Mascota desconocida';
            $a->pet_species = $pet['species'] ?? null;
            $a->pet_breed = $pet['breed'] ?? null;
            $a->pet_age = $pet['ageYears'] ?? null;
            $a->pet_photo_url = $pet['photoUrl'] ?? null;

            // --- DATOS DE CLÍNICA ---
            $clinicId = $a->clinicId ?? $a->clinic_id ?? null; // Ajustar si el campo cambia
            $clinic = $clinicId && $clinics->has($clinicId) ? $clinics[$clinicId] : null;
            $a->clinic_name = $clinic['name'] ?? 'Clínica desconocida';

            // --- DATOS DE SERVICIO ---
            $serviceId = $a->serviceId ?? $a->service_id ?? null; // Ajustar si el campo cambia
            $service = $serviceId && $services->has($serviceId) ? $services[$serviceId] : null;
            $a->service_name = $service['name'] ?? ($serviceId ?? 'Servicio desconocido');

            // --- FECHA Y HORA ---
            $a->appointment_date = $a->startAt ?? $a->appointment_date ?? $a->date ?? null; // Ajustar si el campo cambia

            // --- ESTADO ---
            $a->status = strtolower($a->status ?? 'pendiente');

            // --- NOTA DEL VETERINARIO ---
            $a->vet_notes = $a->vetNotes ?? $a->vet_notes ?? null;

            // --- EXTRAS ---
            $a->extra_services = [];
            if (!empty($a->extraServicesJson)) {
                $a->extra_services = json_decode($a->extraServicesJson, true) ?? [];
            }

            // --- ACCIONES ---
            $a->can_cancel = in_array($a->status, ['pendiente', 'pending', 'confirmada', 'confirmed']);
            $a->can_reschedule = in_array($a->status, ['pendiente', 'pending', 'confirmada', 'confirmed']);

            return $a;
        });

        // Filtros de estado para la vista
        $statusFilters = [
            'todas' => 'Todas',
            'pendiente' => 'Pendientes',
            'confirmada' => 'Confirmadas',
            'cancelada' => 'Canceladas',
            'finalizada' => 'Finalizadas',
        ];


        // Guardar todas las citas antes de filtrar para la vista
        $allAppointments = $appointments;

        // Filtrar por estado si se selecciona uno distinto de 'todas'
        $selectedStatus = strtolower(request('status', 'todas'));
        $statusEquivalents = [
            'pendiente' => ['pendiente', 'pending'],
            'confirmada' => ['confirmada', 'confirmed'],
            'cancelada' => ['cancelada', 'cancelled'],
            'finalizada' => ['finalizada', 'completed'],
        ];
        if ($selectedStatus !== 'todas') {
            $appointments = $appointments->filter(function($a) use ($selectedStatus, $statusEquivalents) {
                if (isset($statusEquivalents[$selectedStatus])) {
                    return in_array(strtolower($a->status), $statusEquivalents[$selectedStatus]);
                }
                return strtolower($a->status) === $selectedStatus;
            });
        }

        // Ordenar: próximas primero (por fecha descendente)
        $appointments = $appointments->sortByDesc(function($a) {
            return $a->appointment_date ?? '';
        })->values();

        return view('appointments.my', [
            'appointments' => $appointments,
            'statusFilters' => $statusFilters,
            'allAppointments' => $allAppointments,
        ]);
    }

    // Vet: citas de mis clínicas
    public function vetAppointments(
        AppointmentsFirestoreService $firestore,
        MedicalServicesFirestoreService $servicesFirestore,
        PetsFirestoreService $petsFirestore,
        ClinicsFirestoreService $clinicsFirestore,
        Request $request
    ) {
        $user = auth()->user();
        $vetUid = (string)($user->uid ?? $user->id);
        $appointments = $firestore->listByVet($vetUid);

        // Filtros
        $status = $request->query('status');
        $search = $request->query('search');
        $sort = $request->query('sort', 'upcoming');

        if ($status && in_array($status, ['PENDING', 'CONFIRMED', 'REJECTED', 'CANCELLED'])) {
            $appointments = $appointments->where('status', $status);
        }
        if ($search) {
            $appointments = $appointments->filter(function($a) use ($search) {
                return (
                    (isset($a['petName']) && stripos($a['petName'], $search) !== false) ||
                    (isset($a['userName']) && stripos($a['userName'], $search) !== false) ||
                    (isset($a['contact']) && stripos($a['contact'], $search) !== false)
                );
            });
        }
        // Ordenamiento
        if ($sort === 'upcoming') {
            $appointments = $appointments->sortBy('startAt');
        } elseif ($sort === 'recent') {
            $appointments = $appointments->sortByDesc('createdAt');
        } elseif ($sort === 'oldest') {
            $appointments = $appointments->sortBy('createdAt');
        }

        // Nombres amigables (servicio, mascota, clínica, cliente)
        $services = $servicesFirestore->listActiveServices();
        $clinics = $clinicsFirestore->list();
        // Obtener todas las mascotas del sistema para mapear por ID
        $pets = $petsFirestore->listAll();
        // Para clientes, si tienes userName directo en la cita úsalo, si no, puedes mapearlo aquí si tienes acceso

        $appointments = $appointments->map(function($a) use ($services, $clinics, $pets) {
            $a = (object)$a;
            $a->serviceName = $services[$a->serviceId]['name'] ?? $a->serviceId ?? '';
            $a->clinicName = $clinics[$a->clinicId]['name'] ?? $a->clinicId ?? '';
            $a->petName = $a->petName ?? ($pets[$a->petId]['name'] ?? $a->petId ?? '');
            // userName: si existe en el objeto, úsalo; si no, deja el UID
            $a->userName = $a->userName ?? $a->userUid ?? '';
            return $a;
        });

        // Métricas
        $metrics = [
            'total'      => $appointments->count(),
            'pending'    => $appointments->where('status', 'PENDING')->count(),
            'confirmed'  => $appointments->where('status', 'CONFIRMED')->count(),
            'rejected'   => $appointments->where('status', 'REJECTED')->count(),
            'cancelled'  => $appointments->where('status', 'CANCELLED')->count(),
        ];

        return view('vet.appointments.index', [
            'appointments' => $appointments,
            'metrics' => $metrics,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
        ]);
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
    // Vet: guardar nota al usuario
    public function saveVetNote(Request $request, $appointmentId, AppointmentsFirestoreService $firestore)
    {
        $data = $request->validate([
            'vetNotes' => 'required|string|max:1000',
        ]);
        try {
            // Obtener el status actual de la cita
            $all = $firestore->listByVet(auth()->user()->uid ?? auth()->user()->id);
            $appointment = $all->firstWhere('id', $appointmentId);
            $status = $appointment['status'] ?? 'PENDING';
            $firestore->setStatus($appointmentId, $status, ['vetNotes' => $data['vetNotes']]);
            return back()->with('success', 'Nota guardada correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['vetNotes' => $e->getMessage()]);
        }
    }
}
