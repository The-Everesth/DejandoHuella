<?php

namespace App\Http\Controllers;
use App\Services\Firestore\PetsFirestoreService;
use Illuminate\Support\Facades\Log;


use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\Request;

class PetController extends Controller
{
    public function index()
    {
        $ownerUid = $this->getOwnerUid();
        Log::info('[PETS] ownerUid index', ['ownerUid' => $ownerUid]);
        $svc = app(PetsFirestoreService::class);
        $pets = $svc->listByOwner($ownerUid);
        return view('pets.index', compact('pets'));
    }
    /**
     * Retorna el UID del usuario autenticado (Firestore UID o fallback a id numérico)
     */
    private function getOwnerUid(): string
    {
        $user = auth()->user();
        return $user->uid ?? (string)$user->id;
    }

    public function create()
    {
        return view('pets.create');
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'species' => 'required|string|max:30',
            'sex' => 'required|string|max:10',
            'breed' => 'nullable|string|max:50',
            'ageYears' => 'nullable|integer|min:0|max:50',
            'notes' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:4096', // Validación igual que adopciones
        ]);

        $data['ownerUid'] = $this->getOwnerUid();
        $data['isActive'] = true;
        $data['createdAt'] = now()->toIso8601String();
        $data['updatedAt'] = now()->toIso8601String();

        $photoFile = $request->file('photo');
        unset($data['photo']); // Nunca guardar la ruta temporal

        $svc = app(\App\Services\Firestore\PetsFirestoreService::class);
        $pet = $svc->createPet($data);
        $petId = $pet['id'] ?? null;

        // Guardar la foto si existe
        if ($photoFile && $photoFile->isValid()) {
            $directory = public_path('uploads/pets');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $filename = \Illuminate\Support\Str::uuid()->toString() . '.' . $photoFile->getClientOriginalExtension();
            $photoFile->move($directory, $filename);

            $photoPath = 'uploads/pets/' . $filename;
            $photoUrl = url($photoPath);

            // Actualizar Firestore con las rutas de la foto
            $svc->updatePet($petId, [
                'photoPath' => $photoPath,
                'photoUrl' => $photoUrl,
            ]);
        }

        return redirect()->route('my.pets')->with('success', 'Mascota registrada.');
    }


    public function edit($petId)
    {
        $svc = app(PetsFirestoreService::class);
        $ownerUid = $this->getOwnerUid();
        $pet = $svc->getById($petId);
        if (!$pet || ($pet['ownerUid'] ?? null) !== $ownerUid || empty($pet['isActive'])) {
            abort(404);
        }
        return view('pets.edit', ['pet' => (object)$pet]);
    }


    public function update(Request $request, string $pet)
    {
        $svc = app(PetsFirestoreService::class);
        $ownerUid = $this->getOwnerUid();
        $petData = $svc->getById($pet);
        if (!$petData || ($petData['ownerUid'] ?? null) !== $ownerUid || empty($petData['isActive'])) {
            abort(404);
        }
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'species' => 'required|string|max:30',
            'sex' => 'required|string|max:10',
            'breed' => 'nullable|string|max:50',
            'ageYears' => 'nullable|integer|min:0|max:50',
            'notes' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:4096',
        ]);
        $photoFile = $request->file('photo');
        unset($data['photo']); // Nunca guardar la ruta temporal

        $svc->updatePet($pet, $data);

        // Guardar la foto si existe
        if ($photoFile && $photoFile->isValid()) {
            $directory = public_path('uploads/pets');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $filename = \Illuminate\Support\Str::uuid()->toString() . '.' . $photoFile->getClientOriginalExtension();
            $photoFile->move($directory, $filename);

            $photoPath = 'uploads/pets/' . $filename;
            $photoUrl = url($photoPath);

            // Actualizar Firestore con las rutas de la foto
            $svc->updatePet($pet, [
                'photoPath' => $photoPath,
                'photoUrl' => $photoUrl,
            ]);
        }

        return redirect()->route('my.pets')->with('success', 'Mascota actualizada correctamente.');
    }


    public function destroy($petId)
    {
        $svc = app(PetsFirestoreService::class);
        $ownerUid = $this->getOwnerUid();
        $pet = $svc->getById($petId);
        if (!$pet || ($pet['ownerUid'] ?? null) !== $ownerUid || empty($pet['isActive'])) {
            abort(404);
        }
        $svc->deletePet($petId);
        return redirect()->route('my.pets')->with('success', 'Mascota eliminada correctamente.');
    }
}
