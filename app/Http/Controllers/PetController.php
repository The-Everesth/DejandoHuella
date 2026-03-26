<?php

namespace App\Http\Controllers;
use App\Services\Firestore\PetsFirestoreService;
use App\Services\CloudinaryService;
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

        $svc = app(PetsFirestoreService::class);
        $pet = $svc->createPet($data);
        $petId = $pet['id'] ?? null;

        // Subir la foto a Cloudinary si existe
        if ($photoFile && $photoFile->isValid()) {
            $cloudinary = app(CloudinaryService::class);
            $secureUrl = $cloudinary->uploadImage($photoFile, 'pets');
            if ($secureUrl) {
                $svc->updatePet($petId, [
                    'photoUrl' => $secureUrl,
                    'photoPath' => null, // Limpiar referencia local
                ]);
            }
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

        // Subir la foto a Cloudinary si existe
        if ($photoFile && $photoFile->isValid()) {
            $cloudinary = app(CloudinaryService::class);
            $secureUrl = $cloudinary->uploadImage($photoFile, 'pets');
            if ($secureUrl) {
                $svc->updatePet($pet, [
                    'photoUrl' => $secureUrl,
                    'photoPath' => null, // Limpiar referencia local
                ]);
            }
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
