<?php

namespace App\Http\Controllers;

use App\Models\AdoptionPost;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\Request;

class MyAdoptionController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        $posts = $user->hasRole('admin')
            ? AdoptionPost::with('pet')->latest()->get()
            : $user->adoptionPosts()->with('pet')->latest()->get();

        return view('myadoptions.index', compact('posts'));
    }

    public function create()
    {
        /** @var User $user */
        $user = auth()->user();

        // Solo tus mascotas que no estén ya publicadas
        $pets = $user->pets()
            ->whereDoesntHave('adoptionPost')
            ->latest()
            ->get();

        return view('myadoptions.create', compact('pets'));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $request->validate([
            'pet_id' => 'required|exists:pets,id',
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
            'requirements' => 'nullable|string|max:1000',
        ]);

        // Asegura que la mascota es del usuario
        $pet = Pet::where('id', $data['pet_id'])
            ->where('owner_id', $user->id)
            ->firstOrFail();

        AdoptionPost::create([
            'pet_id' => $pet->id,
            'created_by' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'requirements' => $data['requirements'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('myadoptions.index')->with('success', 'Publicación creada.');
    }

    public function toggle(AdoptionPost $post)
    {
        $this->authorize('update', $post);

        $post->update(['is_active' => !$post->is_active]);

        return back()->with('success', 'Estado actualizado.');
    }

    public function requests(AdoptionPost $post)
    {
        $this->authorize('viewRequests', $post);

        $post->load(['pet', 'requests.applicant']);

        return view('myadoptions.requests', compact('post'));
    }
}
