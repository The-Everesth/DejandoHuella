<?php

namespace App\Http\Controllers;

use App\Models\AdoptionPost;

class AdoptionPublicController extends Controller
{
    public function index()
    {
        $posts = AdoptionPost::query()
            ->where('is_active', true)
            ->with(['pet.owner'])
            ->latest()
            ->get();

        return view('adoptions.index', compact('posts'));
    }

    public function show(AdoptionPost $post)
    {
        abort_unless($post->is_active, 404);

        $post->load(['pet.owner', 'requests']);

        return view('adoptions.show', compact('post'));
    }
}
