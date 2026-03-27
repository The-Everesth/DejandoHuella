<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Firestore\PetsFirestoreService;
use App\Services\Firestore\AppointmentsFirestoreService;
use App\Services\Firestore\ClinicsFirestoreService;
use App\Services\Firestore\MedicalServicesFirestoreService;
use App\Services\Firestore\AdoptionsFirestoreService;
use App\Services\Firestore\AdoptionRequestsFirestoreService;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }


        // Determinar el rol principal por prioridad usando el servicio directamente
        $roleService = app(\App\Services\Firestore\FirestoreUserRoleService::class);
        $roles = $roleService->getRolesByUser($user);
        $mainRole = 'ciudadano';
        if (in_array('admin', $roles, true)) {
            $mainRole = 'admin';
        } elseif (in_array('veterinario', $roles, true)) {
            $mainRole = 'veterinario';
        } elseif (in_array('refugio', $roles, true)) {
            $mainRole = 'refugio';
        } elseif (in_array('institucion', $roles, true)) {
            $mainRole = 'institucion';
        } elseif (in_array('ciudadano', $roles, true)) {
            $mainRole = 'ciudadano';
        }

        // Servicios Firestore
        $petsService = app(PetsFirestoreService::class);
        $appointmentsService = app(AppointmentsFirestoreService::class);
        $clinicsService = app(ClinicsFirestoreService::class);
        $medicalService = app(MedicalServicesFirestoreService::class);
        $adoptionsService = app(AdoptionsFirestoreService::class);
        $adoptionRequestsService = app(AdoptionRequestsFirestoreService::class);

        // Datos por rol
        $data = [
            'mainRole' => $mainRole,
            'user' => $user,
        ];

        if ($mainRole === 'ciudadano') {
            $ownerUid = $user->uid ?? (string)$user->id;
            $pets = $petsService->listByOwner($ownerUid);
            // Traer todas las citas y filtrar por usuario y estado pendiente
            $allAppointments = method_exists($appointmentsService, 'list') ? $appointmentsService->list() : [];
            $appointments = collect($allAppointments)->filter(function($a) use ($ownerUid) {
                return ($a['ownerUid'] ?? $a['userUid'] ?? $a['user_id'] ?? null) == $ownerUid;
            })->values();
            $pendingAppointments = $appointments->filter(function($a) {
                $status = strtolower($a['status'] ?? '');
                return in_array($status, ['pendiente','pending']);
            })->sortByDesc('startAt')->values();

            // Mapear servicios, clínicas y mascotas para mostrar nombres en la vista
            $services = method_exists($medicalService, 'listActiveServices') ? $medicalService->listActiveServices() : [];
            $servicesById = [];
            foreach ($services as $srv) {
                if (isset($srv['id'])) $servicesById[$srv['id']] = $srv;
            }
            $allClinics = method_exists($clinicsService, 'list') ? $clinicsService->list() : [];
            $clinicsById = [];
            foreach ($allClinics as $cl) {
                if (isset($cl['id'])) $clinicsById[$cl['id']] = $cl;
            }
            $allPets = method_exists($petsService, 'listAll') ? $petsService->listAll() : [];
            $petsById = [];
            foreach ($allPets as $pet) {
                if (isset($pet['id'])) $petsById[$pet['id']] = $pet;
            }
            // Enriquecer las citas pendientes con nombres
            $pendingAppointments = $pendingAppointments->map(function($appt) use ($servicesById, $clinicsById, $petsById) {
                // Servicio
                if (empty($appt['service_name']) && !empty($appt['serviceId']) && isset($servicesById[$appt['serviceId']])) {
                    $appt['service_name'] = $servicesById[$appt['serviceId']]['name'] ?? $appt['serviceId'];
                }
                // Clínica
                if (!empty($appt['clinicId']) && isset($clinicsById[$appt['clinicId']])) {
                    $appt['clinic_name'] = $clinicsById[$appt['clinicId']]['name'] ?? $appt['clinicId'];
                }
                // Mascota
                if (!empty($appt['petId']) && isset($petsById[$appt['petId']])) {
                    $appt['pet_name'] = $petsById[$appt['petId']]['name'] ?? $appt['petId'];
                }
                return $appt;
            });
            // Solicitudes de adopción hechas por el usuario (todas)
            $adoptionRequests = method_exists($adoptionRequestsService, 'listByApplicant')
                ? $adoptionRequestsService->listByApplicant($user->id)
                : [];
            $pendingAdoptionRequests = collect($adoptionRequests)->filter(function($r) {
                $status = strtolower($r['status'] ?? '');
                return in_array($status, ['pendiente','pending']);
            });
            // Para la sección de actividad reciente: mostrar las solicitudes pendientes más recientes hechas por el usuario
            // Se necesita el nombre del animal asociado a la adopción
            $adoptionIds = $pendingAdoptionRequests->pluck('adoptionId')->filter()->unique()->values()->all();
            $adoptionsById = [];
            if (!empty($adoptionIds)) {
                $adoptions = method_exists($adoptionsService, 'list') ? $adoptionsService->list() : [];
                foreach ($adoptions as $adopt) {
                    if (isset($adopt['id']) && in_array($adopt['id'], $adoptionIds)) {
                        $adoptionsById[$adopt['id']] = $adopt;
                    }
                }
            }
            // Obtener nombres de mascotas
            $petIds = collect($adoptionsById)->pluck('petId')->filter()->unique()->values()->all();
            $petsById = [];
            if (!empty($petIds)) {
                $allPets = method_exists($petsService, 'listAll') ? $petsService->listAll() : [];
                foreach ($allPets as $pet) {
                    if (isset($pet['id']) && in_array($pet['id'], $petIds)) {
                        $petsById[$pet['id']] = $pet;
                    }
                }
            }
            $recentRequests = $pendingAdoptionRequests->sortByDesc('createdAt')->take(3)->map(function($req) use ($adoptionsById, $petsById) {
                $adoption = isset($adoptionsById[$req['adoptionId'] ?? '']) ? $adoptionsById[$req['adoptionId']] : null;
                $petName = null;
                if ($adoption && isset($adoption['petId']) && isset($petsById[$adoption['petId']])) {
                    $petName = $petsById[$adoption['petId']]['name'] ?? null;
                }
                return [
                    'petName' => $petName,
                    'applicantName' => $req['applicantName'] ?? null,
                    'createdAt' => $req['createdAt'] ?? null,
                    'status' => $req['status'] ?? null,
                ];
            });
            $data += [
                'petsCount' => count($pets),
                'pendingAppointmentsCount' => $pendingAppointments->count(),
                'pendingAdoptionRequestsCount' => $pendingAdoptionRequests->count(),
                'recentPets' => collect($pets)->sortByDesc('createdAt')->take(3),
                'recentAppointments' => collect($appointments)->sortByDesc('startAt')->take(3),
                'recentRequests' => $recentRequests,
                'pendingAppointments' => $pendingAppointments,
            ];
        } elseif ($mainRole === 'veterinario') {
            // Corregir contadores: clínicas, servicios, adopciones, mascotas
            // Contar todas las clínicas que le pertenecen al veterinario
            // Solo clínicas que aparecen en "Mis Clínicas" (ownerUserId = veterinario)
            $allClinics = method_exists($clinicsService, 'list') ? $clinicsService->list() : [];
            $myClinics = collect($allClinics)->filter(function($c) use ($user) {
                return isset($c['ownerUserId']) && (string)$c['ownerUserId'] === (string)$user->id;
            })->values();
            $services = method_exists($medicalService, 'listActiveServices')
                ? $medicalService->listActiveServices()
                : [];
            // Mapear servicios por id para lookup rápido
            $servicesById = is_array($services) ? $services : [];
            $appointments = method_exists($appointmentsService, 'list')
                ? collect($appointmentsService->list())->filter(function($a) use ($user) {
                    return ($a['vetUid'] ?? $a['vet_id'] ?? null) == ($user->uid ?? $user->id);
                })
                : collect();

            // Solo citas pendientes de confirmar/rechazar
            $pendingAppointments = $appointments->filter(function($a) {
                $status = strtolower($a['status'] ?? '');
                return in_array($status, ['pendiente','pending']);
            })->sortBy('startAt')->map(function($appt) use ($servicesById) {
                // Agregar el nombre del servicio si solo hay ID
                if (empty($appt['service_name']) && !empty($appt['serviceId']) && isset($servicesById[$appt['serviceId']])) {
                    $appt['service_name'] = $servicesById[$appt['serviceId']]['name'] ?? $appt['serviceId'];
                }
                return $appt;
            });

            // Adopciones y solicitudes asociadas al veterinario (como publisher)
            $adoptions = method_exists($adoptionsService, 'list') ? $adoptionsService->list() : [];
            $myAdoptions = collect($adoptions)->filter(function($a) use ($user) {
                return ($a['createdBy'] ?? null) == ($user->uid ?? $user->id);
            });
            // Obtener solicitudes recibidas para las adopciones del veterinario
            $adoptionRequests = method_exists($adoptionRequestsService, 'listByAdoptionIds') && $myAdoptions->count() > 0
                ? $adoptionRequestsService->listByAdoptionIds($myAdoptions->pluck('id')->all())
                : [];
            // Solo solicitudes pendientes
            $pendingRequests = collect($adoptionRequests)->filter(function($r) {
                $status = strtolower($r['status'] ?? '');
                return in_array($status, ['pendiente','pending']);
            })->sortByDesc('createdAt');

            // Resumen para solicitudes: buscar nombre mascota y adoptante
            $petsById = [];
            foreach ($myAdoptions as $adopt) {
                if (isset($adopt['petId'])) {
                    $petsById[$adopt['petId']] = null; // se llenará abajo si es necesario
                }
            }
            if (!empty($petsById)) {
                $allPets = method_exists($petsService, 'listAll') ? $petsService->listAll() : [];
                foreach ($allPets as $pet) {
                    if (isset($pet['id']) && array_key_exists($pet['id'], $petsById)) {
                        $petsById[$pet['id']] = $pet;
                    }
                }
            }

            // Preparar resumen de solicitudes
            $pendingRequestsSummary = $pendingRequests->map(function($req) use ($myAdoptions, $petsById) {
                $adoption = $myAdoptions->firstWhere('id', $req['adoptionId'] ?? null);
                $petName = null;
                if ($adoption && isset($adoption['petId']) && isset($petsById[$adoption['petId']]) && $petsById[$adoption['petId']]) {
                    $petName = $petsById[$adoption['petId']]['name'] ?? null;
                }
                return [
                    'id' => $req['id'] ?? null,
                    'adoptionId' => $req['adoptionId'] ?? null,
                    'petName' => $petName,
                    'applicantName' => $req['applicantName'] ?? null,
                    'createdAt' => $req['createdAt'] ?? null,
                    'message' => $req['message'] ?? null,
                    'status' => $req['status'] ?? null,
                ];
            });

            $data += [
                'clinicsCount' => $myClinics->count(),
                'servicesCount' => is_array($services) ? count($services) : 0,
                'adoptionsCount' => $myAdoptions->count(),
                'todayAppointments' => $appointments->filter(function($a) {
                    return isset($a['startAt']) && substr($a['startAt'],0,10) === date('Y-m-d');
                })->take(3),
                'recentClinics' => $myClinics->take(3),
                'recentServices' => is_array($services) ? array_slice($services,0,3) : [],
                'recentAppointments' => $pendingAppointments->take(3),
                'recentAdoptions' => $myAdoptions->sortByDesc('createdAt'),
                'recentRequests' => $pendingRequestsSummary,
            ];
        } elseif ($mainRole === 'refugio' || $mainRole === 'institucion') {
            $adoptions = method_exists($adoptionsService, 'list') ? $adoptionsService->list() : [];
            $myAdoptions = collect($adoptions)->filter(function($a) use ($user) {
                return ($a['createdBy'] ?? null) == ($user->uid ?? $user->id);
            });
            $adoptionIds = $myAdoptions->pluck('id')->filter()->values()->all();
            $adoptionRequests = method_exists($adoptionRequestsService, 'listByAdoptionIds') ? $adoptionRequestsService->listByAdoptionIds($adoptionIds) : [];
            $pendingRequests = collect($adoptionRequests)->filter(function($req) {
                $status = strtolower($req['status'] ?? '');
                return in_array($status, ['pendiente','pending']);
            });
            // Enriquecer las adopciones con nombre y datos de mascota
            $petIds = $myAdoptions->pluck('petId')->filter()->unique()->values()->all();
            $petsById = [];
            if (!empty($petIds)) {
                $allPets = method_exists($petsService, 'listAll') ? $petsService->listAll() : [];
                foreach ($allPets as $pet) {
                    if (isset($pet['id']) && in_array($pet['id'], $petIds)) {
                        $petsById[$pet['id']] = $pet;
                    }
                }
            }
            // DEBUG: Mostrar datos de adopciones y solicitudes para el usuario refugio
            if (in_array($mainRole, ['refugio', 'institucion'])) {
                \Log::info('[DEBUG Dashboard] Usuario: ' . ($user->uid ?? $user->id));
                \Log::info('[DEBUG Dashboard] Adopciones encontradas: ', $myAdoptions->toArray());
                \Log::info('[DEBUG Dashboard] Solicitudes encontradas: ', $adoptionRequests);
                \Log::info('[DEBUG Dashboard] Solicitudes pendientes: ', $pendingRequests->toArray());
            }
            // PAGINACIÓN MANUAL (colección, no Eloquent):
            $page = request()->input('adopciones_page', 1);
            $perPage = 6;
            $sortedAdoptions = $myAdoptions->sortByDesc('createdAt')->values();
            $total = $sortedAdoptions->count();
            $adoptionsPage = $sortedAdoptions->slice(($page - 1) * $perPage, $perPage)->map(function($adoption) use ($petsById) {
                $petName = null;
                $pet = [];
                if (!empty($adoption['petId']) && isset($petsById[$adoption['petId']]) && $petsById[$adoption['petId']]) {
                    $pet = $petsById[$adoption['petId']];
                    $petName = $pet['name'] ?? null;
                }
                // Para cada campo relevante, si el de adopción está vacío, usar el de la mascota
                $fields = ['breed', 'age', 'description'];
                $result = $adoption;
                foreach ($fields as $field) {
                    if (empty($adoption[$field]) && !empty($pet[$field])) {
                        $result[$field] = $pet[$field];
                    }
                }
                $result['petName'] = $petName ?? 'Adopción';
                return $result;
            });
            // Crear objeto LengthAwarePaginator manualmente
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $adoptionsPage,
                $total,
                $perPage,
                $page,
                [
                    'pageName' => 'adopciones_page',
                    'path' => url()->current(),
                ]
            );
            $data += [
                'adoptionsCount' => $myAdoptions->count(),
                'pendingRequestsCount' => $pendingRequests->count(),
                'adoptionsPaginator' => $paginator,
                'recentRequests' => $pendingRequests->sortByDesc('createdAt')->take(6),
            ];
        } elseif ($mainRole === 'admin') {
            // Admin dashboard sigue separado
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard', $data);
    }
}