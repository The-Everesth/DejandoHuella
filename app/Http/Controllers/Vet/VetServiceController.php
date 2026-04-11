<?php
namespace App\Http\Controllers\Vet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Firestore\MedicalServicesFirestoreService;


class VetServiceController extends Controller
{
    protected $firestore;

    public function __construct(MedicalServicesFirestoreService $firestore)
    {
        $this->firestore = $firestore;
    }

    public function index()
    {
        $vetId = Auth::id();
        $all = $this->firestore->listActiveServices();
        $services = collect($all)->filter(fn($s) => ($s['vet_id'] ?? null) == $vetId)->values();
        return view('vet.services.index', ['services' => $services]);
    }

    public function create()
    {
        return view('vet.services.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
        ]);
        $data['vet_id'] = Auth::id();
        $data['is_active'] = true;
        $data['createdAt'] = now()->toIso8601String();
        $data['updatedAt'] = now()->toIso8601String();
        $this->firestore->createService($data);
        return redirect()->route('vet.services.index')->with('success', 'Servicio creado correctamente.');
    }

    public function edit($id)
    {
        $service = $this->findOrFail($id);
        $this->authorizeService($service);
        return view('vet.services.edit', compact('service'));
    }

    public function update(Request $request, $id)
    {
        $service = $this->findOrFail($id);
        $this->authorizeService($service);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
        ]);
        $data['updatedAt'] = now()->toIso8601String();
        $this->firestore->updateService($id, $data);
        return redirect()->route('vet.services.index')->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy($id)
    {
        $service = $this->findOrFail($id);
        $this->authorizeService($service);
        $this->firestore->deleteService($id);
        return redirect()->route('vet.services.index')->with('success', 'Servicio eliminado correctamente.');
    }

    private function findOrFail($id)
    {
        $all = $this->firestore->listActiveServices();
        $service = $all[$id] ?? null;
        if (!$service) abort(404);
        return $service;
    }

    private function authorizeService($service)
    {
        if (($service['vet_id'] ?? null) != Auth::id()) {
            abort(403, 'No tienes permiso para gestionar este servicio.');
        }
    }
}
