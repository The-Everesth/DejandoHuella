<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;

class SupportTicketAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'pendiente'); // default
        $priority = $request->query('priority'); // null si no viene

        $tickets = SupportTicket::query()
            ->with(['user'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('subject', 'like', "%{$q}%")
                        ->orWhere('message', 'like', "%{$q}%");
                });
            })
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($priority, function ($query) use ($priority) {
                $query->where('priority', $priority);
            })
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $pendingCount = SupportTicket::query()
            ->where('status', 'pendiente')
            ->count();

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
