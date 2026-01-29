<?php

namespace App\Http\Controllers\Vet;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\MedicalService;
use Illuminate\Http\Request;

class ClinicServiceController extends Controller
{
    public function edit(Clinic $clinic)
    {
        // Seguridad: que el vet solo edite SU clínica
        abort_unless($clinic->user_id === auth()->id(), 403);

        $services = MedicalService::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selected = $clinic->services()->pluck('medical_services.id')->toArray();

        return view('vet.clinics.services.edit', compact('clinic', 'services', 'selected'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        abort_unless($clinic->user_id === auth()->id(), 403);

        $data = $request->validate([
            'services' => ['array'],
            'services.*.enabled' => ['nullable', 'in:on,1,true'],
            'services.*.price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'services.*.duration_minutes' => ['nullable', 'integer', 'min:5', 'max:600'],
            'custom_service' => ['nullable','string','max:80'],
            'custom_price' => ['nullable','numeric','min:0','max:999999.99'],
            'custom_duration_minutes' => ['nullable','integer','min:5','max:600'],
        ]);

        $sync = [];

        // servicios existentes
        foreach (($data['services'] ?? []) as $serviceId => $row) {
            $enabled = isset($row['enabled']);
            if (!$enabled) continue;

            $sync[$serviceId] = [
                'price' => $row['price'] ?? null,
                'currency' => 'MXN',
                'duration_minutes' => $row['duration_minutes'] ?? null,
                'is_available' => true,
            ];
        }

        // servicio custom
        $custom = trim((string)($data['custom_service'] ?? ''));
        if ($custom !== '') {
            $service = MedicalService::firstOrCreate(
                ['name' => $custom],
                ['type' => 'custom', 'created_by' => auth()->id(), 'is_active' => true]
            );

            $sync[$service->id] = [
                'price' => $data['custom_price'] ?? null,
                'currency' => 'MXN',
                'duration_minutes' => $data['custom_duration_minutes'] ?? null,
                'is_available' => true,
            ];
        }

        $clinic->services()->sync($sync);

        return back()->with('success', 'Servicios y precios actualizados.');
    }

}
