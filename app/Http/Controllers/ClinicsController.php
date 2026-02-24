<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Firestore\ClinicsFirestoreService;
use App\Services\Firestore\MedicalServicesFirestoreService;

class ClinicsController extends Controller
{
    protected $clinics;
    protected $services;

    public function __construct(ClinicsFirestoreService $clinics, MedicalServicesFirestoreService $services)
    {
        $this->clinics = $clinics;
        $this->services = $services;
    }

    // Web: catálogo view
    public function catalog()
    {
        return view('clinics.index');
    }

    public function catalogByService(string $service)
    {
        return redirect()->route('services.index', ['serviceId' => $service]);
    }

    // Web: detalle view (from Firestore collection clinicas)
    public function showView(string $id)
    {
        $clinic = $this->clinics->getClinicById($id);
        $isPublished = false;
        if (is_array($clinic)) {
            if (array_key_exists('published', $clinic)) {
                $isPublished = filter_var($clinic['published'], FILTER_VALIDATE_BOOLEAN);
            } elseif (array_key_exists('is_public', $clinic)) {
                $isPublished = filter_var($clinic['is_public'], FILTER_VALIDATE_BOOLEAN);
            }
        }

        if (! $clinic || ! $isPublished) {
            abort(404);
        }

        $allServices = $this->services->listActiveServices();
        $serviceIds = is_array($clinic['services'] ?? null) ? $clinic['services'] : [];

        $servicesDetailed = [];
        foreach ($serviceIds as $serviceId) {
            if (isset($allServices[$serviceId])) {
                $servicesDetailed[] = [
                    'id' => $allServices[$serviceId]['id'] ?? $serviceId,
                    'name' => $allServices[$serviceId]['name'] ?? 'Servicio',
                    'description' => $allServices[$serviceId]['description'] ?? null,
                ];
            } else {
                $servicesDetailed[] = [
                    'id' => $serviceId,
                    'name' => $serviceId,
                    'description' => null,
                ];
            }
        }

        $ownerName = null;
        if (! empty($clinic['ownerUserId'])) {
            $owner = \App\Models\User::find((int) $clinic['ownerUserId']);
            $ownerName = $owner ? $owner->name : null;
        }

        $clinic['servicesDetailed'] = $servicesDetailed;

        return view('clinics.show', ['clinic' => $clinic, 'ownerName' => $ownerName]);
    }

    // Vet panel view
    public function vetForm()
    {
        $user = Auth::user();
        if (! $user || (! $user->hasRole('veterinario') && ! $user->hasRole('admin'))) {
            abort(403);
        }

        $ownerUserId = $user->id;
        if ($user->hasRole('admin') && request()->filled('ownerUserId')) {
            $ownerUserId = (int) request()->query('ownerUserId');
        }

        $clinic = $this->clinics->getClinicByOwnerUserId($ownerUserId);
        $services = $this->services->listActiveServices();
        return view('clinics.vet', ['clinic' => $clinic, 'services' => $services]);
    }

    // API: list
    public function index(Request $request)
    {
        $filters = [];
        if ($request->has('serviceId')) {
            $filters['serviceId'] = $request->get('serviceId');
        }
        if ($request->has('q')) {
            $filters['q'] = $request->get('q');
        }
        $list = $this->clinics->listPublishedClinics($filters);
        return response()->json(['success' => true, 'data' => array_values($list)]);
    }

    // API: show
    public function show(string $id)
    {
        $clinic = $this->clinics->getClinicById($id);
        if (! $clinic) {
            return response()->json(['success' => false, 'message' => 'Clínica no encontrada'], 404);
        }

        $isPublished = false;
        if (array_key_exists('published', $clinic)) {
            $isPublished = filter_var($clinic['published'], FILTER_VALIDATE_BOOLEAN);
        } elseif (array_key_exists('is_public', $clinic)) {
            $isPublished = filter_var($clinic['is_public'], FILTER_VALIDATE_BOOLEAN);
        }
        if (! $isPublished) {
            $user = Auth::user();
            $isAdmin = $user && $user->hasRole('admin');
            $isOwnerVet = $user && $user->hasRole('veterinario') && isset($clinic['ownerUserId']) && (int) $clinic['ownerUserId'] === (int) $user->id;

            if (! $isAdmin && ! $isOwnerVet) {
                return response()->json(['success' => false, 'message' => 'Clínica no encontrada'], 404);
            }
        }

        return response()->json(['success' => true, 'data' => $clinic]);
    }

    // Vet: store (create/update)
    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user || (! $user->hasRole('veterinario') && ! $user->hasRole('admin'))) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'services' => 'nullable|array',
            'services.*' => 'string|max:120',
            'published' => 'nullable|boolean',
            'phone' => 'nullable|string|max:50',
            'ownerUserId' => 'nullable|integer|min:1',
        ]);

        $data = $validated;
        $data['services'] = array_values(array_unique($validated['services'] ?? []));
        $data['published'] = $validated['published'] ?? false;
        unset($data['ownerUserId']);

        $ownerUserId = (int) $user->id;
        if ($user->hasRole('admin') && ! empty($validated['ownerUserId'])) {
            $ownerUserId = (int) $validated['ownerUserId'];
        }

        try {
            \Log::info('ClinicsController::store() - Saving clinic', ['ownerUserId' => $ownerUserId, 'data' => $data]);
            $clinic = $this->clinics->createOrUpdateClinicForVet($ownerUserId, $data);
            \Log::info('ClinicsController::store() - Clinic saved successfully', ['clinic' => $clinic]);
            return response()->json(['success' => true, 'data' => $clinic, 'message' => 'Clínica guardada exitosamente']);
        } catch (\Throwable $e) {
            \Log::error('ClinicsController::store() - Error saving clinic', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Vet: delete
    public function destroy(Request $request)
    {
        $user = Auth::user();
        if (! $user || (! $user->hasRole('veterinario') && ! $user->hasRole('admin'))) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }
        $clinicId = $request->get('clinicId') ?? ('clinic_u_'.$user->id);
        try {
            $this->clinics->deleteClinic($clinicId, (int) $user->id, $user->hasRole('admin'));
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
