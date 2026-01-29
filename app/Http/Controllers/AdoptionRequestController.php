<?php

namespace App\Http\Controllers;

use App\Models\AdoptionPost;
use App\Models\AdoptionRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AdoptionRequestController extends Controller
{
    public function store(Request $request, AdoptionPost $post)
    {
        abort_unless($post->is_active, 404);

        $this->authorize('create', [AdoptionRequest::class, $post]);

        /** @var User $user */
        $user = auth()->user();

        $data = $request->validate([
            'message' => 'nullable|string|max:1000',
        ]);

        AdoptionRequest::create([
            'adoption_post_id' => $post->id,
            'applicant_id' => $user->id,
            'message' => $data['message'] ?? null,
            'status' => 'pendiente',
        ]);

        return back()->with('success', 'Solicitud enviada.');
    }

    public function myRequests()
    {
        /** @var User $user */
        $user = auth()->user();

        $requests = $user->adoptionRequests()
            ->with(['post.pet'])
            ->latest()
            ->get();

        return view('myrequests.index', compact('requests'));
    }

    public function setStatus(Request $request, AdoptionRequest $adoptionRequest)
    {
        $this->authorize('updateStatus', $adoptionRequest);

        $data = $request->validate([
            'status' => 'required|in:aprobada,rechazada',
        ]);

        $adoptionRequest->update(['status' => $data['status']]);

        return back()->with('success', 'Solicitud actualizada.');
    }
}
