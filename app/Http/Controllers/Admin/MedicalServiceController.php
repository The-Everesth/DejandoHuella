<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalService;
use Illuminate\Http\Request;

class MedicalServiceController extends Controller
{
    public function index()
    {
        $services = MedicalService::latest()->get();
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:1000',
            'base_price' => 'nullable|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
        ]);

        MedicalService::create($data);
        return redirect()->route('admin.services.index')->with('success', 'Servicio creado.');
    }

    public function edit(MedicalService $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, MedicalService $service)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:1000',
            'base_price' => 'nullable|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
        ]);

        $service->update($data);
        return redirect()->route('admin.services.index')->with('success', 'Servicio actualizado.');
    }

    public function destroy(MedicalService $service)
    {
        $service->delete();
        return back()->with('success', 'Servicio eliminado.');
    }
}
