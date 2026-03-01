<?php

namespace App\Http\Controllers\Vet;

use App\Http\Controllers\Controller;
use App\Services\Firestore\ClinicsFirestoreService;
use App\Services\Firestore\MedicalServicesFirestoreService;
use Illuminate\Http\Request;

class ClinicServiceController extends Controller
{
    public function edit($clinicId, ClinicsFirestoreService $clinicsFirestore, MedicalServicesFirestoreService $servicesFirestore)
    {
        \Log::info('[SERVICIOS] entro edit', [
            'user_id' => auth()->id(),
            'user_roles' => method_exists(auth()->user(), 'getRoleNames') ? auth()->user()->getRoleNames() : null,
            'clinic_param' => $clinicId
        ]);

        // resto del código...
        $clinic = $clinicsFirestore->getClinicById($clinicId);
        $ownerId = auth()->id();
        $isOwner = ($clinic['userId'] ?? null) == $ownerId || ($clinic['ownerUserId'] ?? null) == $ownerId;
        abort_unless($clinic && $isOwner, 403);

        $services = array_values($servicesFirestore->listActiveServices());
        $selectedServiceIds = $clinic['serviceIds'] ?? [];

        return view('vet.clinics.services.edit', [
            'clinic' => (object)$clinic,
            'clinicId' => $clinicId,
            'services' => $services,
            'selectedServiceIds' => $selectedServiceIds,
        ]);
    }

    public function update(Request $request, $clinicId, ClinicsFirestoreService $clinicsFirestore, MedicalServicesFirestoreService $servicesFirestore)
    {
        $clinic = $clinicsFirestore->getClinicById($clinicId);
        $ownerId = auth()->id();
        $isOwner = ($clinic['userId'] ?? null) == $ownerId || ($clinic['ownerUserId'] ?? null) == $ownerId;
        abort_unless($clinic && $isOwner, 403);

        $data = $request->validate([
            'service_ids' => ['array'],
            'service_ids.*' => ['string'],
        ]);

        $allServices = $servicesFirestore->listActiveServices();
        $validIds = array_keys($allServices);
        $selected = array_values(array_filter($data['service_ids'] ?? [], fn($id) => in_array($id, $validIds, true)));

        $clinicsFirestore->updateClinicServices($clinicId, $selected);

        return back()->with('success', 'Servicios de la clínica actualizados correctamente.');
    }

}
