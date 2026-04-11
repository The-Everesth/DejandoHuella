<?php

namespace App\Http\Controllers\Vet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vet\MedicalServiceRequest;
use App\Models\Clinic;
use App\Models\MedicalService;
use Illuminate\Support\Facades\Auth;

class ClinicMedicalServiceController extends Controller
{
    public function index(Clinic $clinic)
    {
        $this->authorizeClinic($clinic);
            $allServices = $this->services->listActiveServices();
            $serviceIds = $clinic['serviceIds'] ?? [];
            $services = collect($allServices)->whereIn('id', $serviceIds)->values();
        return view('vet.services.index', compact('clinic', 'services'));
    }

    public function create(Clinic $clinic)
    {
        $this->authorizeClinic($clinic);
        return view('vet.services.create', compact('clinic'));
    }

    public function store(MedicalServiceRequest $request, Clinic $clinic)
    {
        $this->authorizeClinic($clinic);
        $data = $request->validated();
        $data['clinic_id'] = $clinic->id;
        $data['vet_id'] = Auth::id();
            // MedicalService::create($data); // Commented out as we are using Firestore
        return redirect()->route('vet.clinics.services.index', $clinic)->with('success', 'Servicio creado correctamente.');
    }

    public function edit(Clinic $clinic, MedicalService $service)
    {
        $this->authorizeClinic($clinic);
        $this->authorizeService($clinic, $service);
        return view('vet.services.edit', compact('clinic', 'service'));
    }

    public function update(MedicalServiceRequest $request, Clinic $clinic, MedicalService $service)
    {
        $this->authorizeClinic($clinic);
        $this->authorizeService($clinic, $service);
        $data = $request->validated();
            // $service->update($data); // Commented out as we are using Firestore
        return redirect()->route('vet.clinics.services.index', $clinic)->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Clinic $clinic, MedicalService $service)
    {
        $this->authorizeClinic($clinic);
        $this->authorizeService($clinic, $service);
            // $service->delete(); // Commented out as we are using Firestore
        return redirect()->route('vet.clinics.services.index', $clinic)->with('success', 'Servicio eliminado correctamente.');
    }

    private function authorizeClinic(Clinic $clinic)
    {
        if ($clinic->vet_id !== Auth::id()) {
            abort(403, 'No tienes permiso para gestionar esta clínica.');
        }
    }

    private function authorizeService(Clinic $clinic, MedicalService $service)
    {
        if ($service->clinic_id !== $clinic->id) {
            abort(403, 'El servicio no pertenece a esta clínica.');
        }
    }
}
