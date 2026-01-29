<?php

namespace App\Http\Controllers\Vet;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
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

        \App\Models\Clinic::create($data);

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

        $clinic->update($data);

        return redirect()->route('vet.clinics.index')->with('success', 'Clínica actualizada.');
    }

    public function destroy(Clinic $clinic)
    {
        $this->authorize('delete', $clinic);
        $clinic->delete();
        return back()->with('success', 'Clínica eliminada.');
    }
}
