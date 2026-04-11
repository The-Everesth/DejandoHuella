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

    public function show($ticketId, FirestoreSupportTicketService $firestoreTickets)
    {
        $user = auth()->user();
        $ticket = collect($firestoreTickets->listByUser($user->id))->firstWhere('id', $ticketId);

        if (!$ticket) {
            abort(404);
        }

        return view('tickets.show', compact('ticket'));
    }
    /**
     * Permite a un admin ver cualquier ticket por su ID (sin importar el usuario).
     */
    public function showAdmin($ticketId, \App\Services\Firestore\FirestoreSupportTicketAdminService $firestoreTickets)
    {
        $ticket = collect($firestoreTickets->listAll())->firstWhere('id', $ticketId);
        if (!$ticket) {
            abort(404);
        }
        return view('admin.tickets.show', compact('ticket'));
    }
}
