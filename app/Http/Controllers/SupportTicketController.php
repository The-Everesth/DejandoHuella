<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        $tickets = $user->supportTickets()
            ->latest()
            ->get();

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('tickets.create');
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $request->validate([
            'subject' => 'required|string|max:120',
            'priority' => 'required|in:baja,media,alta',
            'message' => 'required|string|max:2000',
        ]);

        $user->supportTickets()->create([
            'subject' => $data['subject'],
            'priority' => $data['priority'],
            'message' => $data['message'],
            'status' => 'pendiente',
        ]);

        return redirect()->route('tickets.index')->with('success', 'Mensaje enviado. Un administrador lo revisará.');
    }

    public function show(SupportTicket $ticket)
    {
        $this->authorize('view', $ticket);

        return view('tickets.show', compact('ticket'));
    }
}
