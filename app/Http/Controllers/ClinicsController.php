<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Firestore\ClinicsFirestoreService;
use App\Services\Firestore\MedicalServicesFirestoreService;

class ClinicsController extends Controller
{
    protected ClinicsFirestoreService $clinics;
    protected MedicalServicesFirestoreService $services;

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

    // Web: detalle view (using Eloquent clinic)
    public function showView(string $id)
    {
        $clinic = \App\Models\Clinic::with(['user', 'services'])->find($id);
        if (!$clinic || !$clinic->is_public) {
            abort(404);
        }
        return view('clinics.show', ['clinic' => $clinic]);
    }

    // Vet panel view
    public function vetForm()
    {
        $user = Auth::user();
        // allow vets and admins
        if (! $user || (! $user->hasRole('veterinario') && ! $user->hasRole('admin'))) {
            abort(403);
        }
        $clinic = $this->clinics->getClinicByOwnerUserId($user->id);
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
            'published' => 'nullable|boolean',
            'phone' => 'nullable|string|max:50',
        ]);

        $data = $validated;
        $data['published'] = $validated['published'] ?? false;

        try {
            \Log::info('ClinicsController::store() - Saving clinic for user ' . $user->id, ['data' => $data]);
            $clinic = $this->clinics->createOrUpdateClinicForVet($user->id, $data);
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
            $this->clinics->deleteClinic($clinicId, $user->id);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
