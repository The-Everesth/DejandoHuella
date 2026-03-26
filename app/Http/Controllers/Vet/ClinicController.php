<?php

namespace App\Http\Controllers\Vet;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use App\Services\Firestore\ClinicsFirestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClinicController extends Controller
{
    protected ClinicsFirestoreService $clinicsFirestore;

    public function __construct(ClinicsFirestoreService $clinicsFirestore)
    {
        $this->clinicsFirestore = $clinicsFirestore;
    }

    public function index()
    {
        // Leer clínicas del veterinario autenticado desde Firestore
        $userId = auth()->id();
        $allClinics = $this->clinicsFirestore->list();
        $clinics = collect($allClinics)
            ->filter(function ($clinic) use ($userId) {
                return isset($clinic['userId']) && $clinic['userId'] == $userId;
            })
            ->values();

        // Simular paginación simple (10 por página)
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $pageItems = $clinics->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $pageItems,
            $clinics->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('vet.clinics.index', ['clinics' => $paginated]);
    }



    public function create()
    {
        return view('vet.clinics.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'phone' => ['nullable','string','max:30'],
            'email' => ['nullable','email','max:120'],
            'address' => ['nullable','string','max:255'],
            'description' => ['nullable','string','max:1000'],
            'opening_hours' => ['nullable','string','max:255'],
            'website' => ['nullable','string','max:255'],
            'is_public' => ['boolean'],
        ]);

        $userId = auth()->id();
        $data['userId'] = $userId;
        $data['is_public'] = $request->boolean('is_public', true);
        $data['published'] = $data['is_public'];

        // Crear clínica en Firestore
        $clinic = $this->clinicsFirestore->createOrUpdateClinicForVet($userId, $data);

        return redirect()->route('vet.clinics.index')->with('success', 'Clínica creada correctamente.');
    }


    public function edit($clinicId)
    {
        $clinic = $this->clinicsFirestore->getClinicById($clinicId);
        $userId = auth()->id();
        abort_unless($clinic && (isset($clinic['userId']) && $clinic['userId'] == $userId), 403);
        return view('vet.clinics.edit', ['clinic' => (object)$clinic]);
    }

    public function update(Request $request, $clinicId)
    {
        $clinic = $this->clinicsFirestore->getClinicById($clinicId);
        $userId = auth()->id();
        abort_unless($clinic && (isset($clinic['userId']) && $clinic['userId'] == $userId), 403);

        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'phone' => ['nullable','string','max:30'],
            'email' => ['nullable','email','max:120'],
            'address' => ['nullable','string','max:255'],
            'description' => ['nullable','string','max:1000'],
            'opening_hours' => ['nullable','string','max:255'],
            'website' => ['nullable','string','max:255'],
            'is_public' => ['nullable','boolean'],
        ]);

        $data['userId'] = $userId;
        $data['is_public'] = $request->boolean('is_public', true);
        $data['published'] = $data['is_public'];

        $this->clinicsFirestore->createOrUpdateClinicForVet($userId, array_merge($clinic, $data));

        return redirect()->route('vet.clinics.index')->with('success', 'Clínica actualizada correctamente.');
    }

    public function destroy($clinicId)
    {
        $clinic = $this->clinicsFirestore->getClinicById($clinicId);
        $userId = auth()->id();
        abort_unless($clinic && (isset($clinic['userId']) && $clinic['userId'] == $userId), 403);
        $this->clinicsFirestore->deleteClinicById($clinicId);
        return back()->with('success', 'Clínica eliminada correctamente.');
    }
}
