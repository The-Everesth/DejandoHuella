<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\MedicalService;
use App\Models\User;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function create(Clinic $clinic, MedicalService $service)
    {
        // Solo si la clínica ofrece el servicio
        $has = $clinic->services()->where('medical_service_id', $service->id)->exists();
        abort_unless($has, 404);

        /** @var User $user */
        $user = auth()->user();
        $pets = $user->pets()->orderBy('name')->get();

        return view('appointments.create', compact('clinic','service','pets'));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'medical_service_id' => 'required|exists:medical_services,id',
            'pet_id' => 'required|exists:pets,id',
            'scheduled_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // mascota debe ser del usuario
        $petOk = $user->pets()->where('id', $data['pet_id'])->exists();
        abort_unless($petOk, 403);

        $clinic = Clinic::findOrFail($data['clinic_id']);
        $service = MedicalService::findOrFail($data['medical_service_id']);

        // clínica debe ofrecer el servicio
        $has = $clinic->services()->where('medical_service_id', $service->id)->exists();
        abort_unless($has, 403);

        Appointment::create([
            'clinic_id' => $clinic->id,
            'medical_service_id' => $service->id,
            'pet_id' => $data['pet_id'],
            'owner_id' => $user->id,
            'vet_id' => null, // MVP: sin asignación directa
            'scheduled_at' => $data['scheduled_at'],
            'status' => 'pendiente',
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('my.appointments')->with('success', 'Cita solicitada.');
    }

    // Ciudadano: mis citas
    public function myAppointments()
    {
        /** @var User $user */
        $user = auth()->user();

        $appointments = $user->appointmentsAsOwner()
            ->with(['clinic','service','pet'])
            ->latest()
            ->get();

        return view('appointments.my', compact('appointments'));
    }

    // Vet: citas de mis clínicas
    public function vetAppointments()
    {
        /** @var User $user */
        $user = auth()->user();

        $clinicIds = $user->clinics()->pluck('id');

        $appointments = Appointment::whereIn('clinic_id', $clinicIds)
            ->with(['clinic','service','pet','owner'])
            ->orderByDesc('scheduled_at')
            ->get();

        return view('vet.appointments.index', compact('appointments'));
    }

    // Vet/Admin: cambiar estado
    public function setStatus(Request $request, Appointment $appointment)
    {
        $this->authorize('updateStatus', $appointment);

        $data = $request->validate([
            'status' => 'required|in:confirmada,atendida,cancelada',
        ]);

        $appointment->update(['status' => $data['status']]);

        return back()->with('success', 'Estado actualizado.');
    }
}
