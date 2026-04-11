<?php

namespace App\Http\Controllers\Vet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Services\Firestore\ClinicsFirestoreService;
use App\Services\Firestore\MedicalServicesFirestoreService;

class ClinicAssignServiceController extends Controller
{
    protected $clinics;
    protected $services;

    public function __construct(ClinicsFirestoreService $clinics, MedicalServicesFirestoreService $services)
    {
        $this->clinics = $clinics;
        $this->services = $services;
    }

    public function edit($clinicId)
    {
        $clinic = $this->clinics->getClinicById($clinicId);
        $this->authorizeClinic($clinic);
        $vetId = Auth::id();
        $services = collect($this->services->listActiveServices())
            ->filter(fn($s) => ($s['vet_id'] ?? null) == $vetId)
            ->values();
        $selected = $clinic['serviceIds'] ?? [];
        return view('vet.clinics.assign-services', compact('clinic', 'services', 'selected'));
    }

    public function update(Request $request, $clinicId)
    {
        $clinic = $this->clinics->getClinicById($clinicId);
        $this->authorizeClinic($clinic);
        $serviceIds = $request->input('services', []);
        $clinic['serviceIds'] = $serviceIds;
        $this->clinics->createOrUpdateClinicForVet($clinic['ownerUserId'], $clinic);
        return redirect()->route('vet.clinics.edit', $clinicId)->with('success', 'Servicios actualizados para la clínica.');
    }

    private function authorizeClinic($clinic)
    {
        $userId = Auth::id();
        if (($clinic['ownerUserId'] ?? null) != $userId && ($clinic['vet_id'] ?? null) != $userId) {
            abort(403, 'No tienes permiso para gestionar esta clínica.');
        }
    }
}
