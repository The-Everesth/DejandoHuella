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
        $clinics = \App\Models\Clinic::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('vet.clinics.index', compact('clinics'));
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

        $data['user_id'] = auth()->id();
        $data['is_public'] = $request->boolean('is_public', true);

        $clinic = \App\Models\Clinic::create($data);

        // Sync to Firestore (dual-write)
        try {
            $this->clinicsFirestore->syncFromModel($clinic);
            Log::info('ClinicController::store() - Clinic synced to Firestore', ['clinicId' => $clinic->id]);
        } catch (\Throwable $e) {
            Log::error('ClinicController::store() - Failed to sync to Firestore', ['error' => $e->getMessage()]);
            // Don't fail the request, MySQL write succeeded
        }

        return redirect()->route('vet.clinics.index')->with('success', 'Clínica creada.');
    }


    public function edit(Clinic $clinic)
    {
        abort_unless($clinic->user_id === auth()->id(), 403);
        return view('vet.clinics.edit', compact('clinic'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        abort_unless($clinic->user_id === auth()->id(), 403);

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

        Log::info('ClinicController::update() - Starting update', [
            'clinicId' => $clinic->id,
            'userId' => auth()->id(),
            'oldName' => $clinic->name,
            'newName' => $data['name'] ?? null,
            'fieldsToUpdate' => array_keys($data),
        ]);

        $clinic->update($data);
        Log::info('ClinicController::update() - After update(), before refresh', [
            'clinicId' => $clinic->id,
            'inMemoryName' => $clinic->name,
        ]);

        $clinic->refresh(); // Refresh model from DB to get updated values
        Log::info('ClinicController::update() - After refresh()', [
            'clinicId' => $clinic->id,
            'refreshedName' => $clinic->name,
            'refreshedPhone' => $clinic->phone,
        ]);

        // Sync to Firestore (dual-write)
        try {
            Log::info('ClinicController::update() - Calling syncFromModel', [
                'clinicId' => $clinic->id,
                'fsDocId' => 'c_' . $clinic->id,
            ]);
            
            $this->clinicsFirestore->syncFromModel($clinic);
            Log::info('ClinicController::update() - Clinic synced to Firestore', ['clinicId' => $clinic->id]);
        } catch (\Throwable $e) {
            Log::error('ClinicController::update() - Failed to sync to Firestore', [
                'clinicId' => $clinic->id,
                'error' => $e->getMessage(),
                'errorCode' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't fail the request, MySQL write succeeded
        }

        return redirect()->route('vet.clinics.index')->with('success', 'Clínica actualizada.');
    }

    public function destroy(Clinic $clinic)
    {
        $this->authorize('delete', $clinic);
        
        // Delete from Firestore first (dual-write strategy)
        try {
            $this->clinicsFirestore->deleteFromModel($clinic);
            Log::info('ClinicController::destroy() - Clinic deleted from Firestore', ['clinicId' => $clinic->id]);
        } catch (\Throwable $e) {
            Log::error('ClinicController::destroy() - Failed to delete from Firestore', ['error' => $e->getMessage()]);
            // Don't fail the request, proceed with MySQL delete
        }
        
        // Then delete from MySQL
        $clinic->delete();
        return back()->with('success', 'Clínica eliminada.');
    }
}
