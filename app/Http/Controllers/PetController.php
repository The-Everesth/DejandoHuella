<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\Request;

class PetController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        $pets = $user->hasRole('admin')
            ? Pet::query()->latest()->get()
            : $user->pets()->latest()->get();

        return view('pets.index', compact('pets'));
    }

    public function create()
    {
        return view('pets.create');
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $request->validate([
            'name' => 'required|string|max:60',
            'species' => 'required|string|max:30',
            'breed' => 'nullable|string|max:60',
            'sex' => 'required|string|max:20',
            'birth_date' => 'nullable|date',
            'color' => 'nullable|string|max:40',
            'description' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
            'is_sterilized' => 'sometimes|boolean',
            'is_vaccinated' => 'sometimes|boolean',
        ]);

        // checkboxes: si no vienen, se consideran false
        $data['is_sterilized'] = (bool) $request->boolean('is_sterilized');
        $data['is_vaccinated'] = (bool) $request->boolean('is_vaccinated');

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('pets', 'public');
        }

        $user->pets()->create($data);

        return redirect()->route('pets.index')->with('success', 'Mascota registrada.');
    }

    public function edit(Pet $pet)
    {
        $this->authorize('update', $pet);
        return view('pets.edit', compact('pet'));
    }

    public function update(Request $request, Pet $pet)
    {
        $this->authorize('update', $pet);

        $data = $request->validate([
            'name' => 'required|string|max:60',
            'species' => 'required|string|max:30',
            'breed' => 'nullable|string|max:60',
            'sex' => 'required|string|max:20',
            'birth_date' => 'nullable|date',
            'color' => 'nullable|string|max:40',
            'description' => 'nullable|string|max:500',
            'is_sterilized' => 'sometimes|boolean',
            'is_vaccinated' => 'sometimes|boolean',
        ]);

        $data['is_sterilized'] = (bool) $request->boolean('is_sterilized');
        $data['is_vaccinated'] = (bool) $request->boolean('is_vaccinated');

        $pet->update($data);

        return redirect()->route('pets.index')->with('success', 'Mascota actualizada.');
    }

    public function destroy(Pet $pet)
    {
        $this->authorize('delete', $pet);
        $pet->delete();

        return redirect()->route('pets.index')->with('success', 'Mascota eliminada.');
    }
}
