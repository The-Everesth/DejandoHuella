<?php

namespace App\Http\Controllers;

use App\Services\Firestore\FirestoreSupportTicketService;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(FirestoreSupportTicketService $firestoreTickets)
    {
        $user = auth()->user();
        $userId = $user && isset($user->id) ? $user->id : null;
        $tickets = [];
        if ($userId) {
            $tickets = collect($firestoreTickets->listByUser($userId));
        } else {
            $tickets = collect();
        }
        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('tickets.create');
    }

    public function store(Request $request, FirestoreSupportTicketService $firestoreTickets)
    {
        $user = auth()->user();
        $userId = $user && isset($user->id) ? $user->id : null;
        $data = $request->validate([
            'subject' => 'required|string|max:120',
            'priority' => 'required|in:baja,media,alta',
            'message' => 'required|string|max:2000',
        ]);
        if ($userId) {
            $firestoreTickets->create($userId, $data);
        }
        return redirect()->route('tickets.index')->with('success', 'Mensaje enviado. Un administrador lo revisará.');
    }

    // La vista de detalle de ticket debe ser adaptada a Firestore si se requiere
}
