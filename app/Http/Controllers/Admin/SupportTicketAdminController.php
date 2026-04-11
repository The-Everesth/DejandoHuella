<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Firestore\FirestoreSupportTicketAdminService;
use Illuminate\Http\Request;
use App\Models\SupportTicket;

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

    public function show($ticketId, FirestoreSupportTicketAdminService $firestoreTickets)
    {
        // Buscar el ticket en Firestore por su id
        $ticket = collect($firestoreTickets->listAll())->firstWhere('id', $ticketId);
        if (!$ticket) {
            abort(404);
        }

        // Aquí puedes agregar lógica de autorización si es necesario

        // marcar como visto si estaba pendiente
        if (($ticket['status'] ?? null) === 'pendiente') {
            $firestoreTickets->update($ticketId, [
                'status' => 'visto',
                'seen_at' => now()->toDateTimeString(),
            ]);
            $ticket['status'] = 'visto';
            $ticket['seen_at'] = now()->toDateTimeString();
        }

        // Si necesitas cargar usuario o admin, deberás hacerlo manualmente aquí

        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, $ticketId, FirestoreSupportTicketAdminService $firestoreTickets)
    {
        $data = $request->validate([
            'admin_reply' => 'required|string',
        ]);

        $admin = auth()->user();

        // Buscar el ticket manualmente en Firestore
        $ticket = collect($firestoreTickets->listAll())->firstWhere('id', $ticketId);
        if (!$ticket) {
            abort(404);
        }

        $firestoreTickets->update($ticketId, [
            'admin_reply' => $data['admin_reply'],
            'answered_by' => $admin->id,
            'answered_at' => now()->toDateTimeString(),
            'status' => 'respondido',
        ]);

        return redirect()->route('admin.tickets.show', $ticketId)
            ->with('success', 'Respuesta guardada correctamente.');
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
