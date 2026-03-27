<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Firestore\FirestoreSupportTicketAdminService;
use Illuminate\Http\Request;

class SupportTicketAdminController extends Controller
{
    public function index(Request $request, FirestoreSupportTicketAdminService $firestoreTickets)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'pendiente');
        $priority = $request->query('priority');

        $filters = [
            'q' => $q,
            'status' => $status,
            'priority' => $priority,
        ];
        $allTickets = collect($firestoreTickets->listAll($filters));
        $pendingCount = $firestoreTickets->countByStatus('pendiente');
        // Simular paginación manual (12 por página)
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 12;
        $tickets = $allTickets->slice(($page-1)*$perPage, $perPage);
        $tickets = new \Illuminate\Pagination\LengthAwarePaginator(
            $tickets,
            $allTickets->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        return view('admin.tickets.index', compact('tickets','q','status','priority','pendingCount'));
    }

    public function show(SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        // marcar como visto si estaba pendiente
        if ($ticket->status === 'pendiente') {
            $ticket->update([
                'status' => 'visto',
                'seen_at' => now(),
            ]);
        }

        $ticket->load(['user', 'answeredBy']);

        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $data = $request->validate([
            'admin_reply' => ['required', 'string', 'max:2000'],
        ]);

        /** @var User $admin */
        $admin = auth()->user();

        $ticket->update([
            'admin_reply' => $data['admin_reply'],
            'answered_by' => $admin->id,
            'answered_at' => now(),
            'status' => 'respondido',
        ]);

        return back()->with('success', 'Respuesta enviada.');
    }

    public function close(SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->update([
            'status' => 'cerrado',
        ]);

        return back()->with('success', 'Ticket cerrado.');
    }
}
