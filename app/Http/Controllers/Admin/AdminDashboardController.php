<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Appointment;
use App\Services\Firestore\AdoptionsFirestoreService;

class AdminDashboardController extends Controller
{
    protected AdoptionsFirestoreService $adoptions;

    public function __construct(AdoptionsFirestoreService $adoptions)
    {
        $this->adoptions = $adoptions;
    }

    public function index()
    {
        // Usuarios activos (Firestore)
        $users = collect(app(\App\Services\Firestore\UsersFirestoreService::class)->list());
        $usersCount = $users->whereNull('deleted_at')->count();

        // Solicitudes pendientes (rol veterinario/refugio)
        $pendingUsers = $users->where('role_request_status', 'pending')
            ->whereIn('requested_role', ['veterinario', 'refugio'])
            ->count();

        // Tickets abiertos 
        $openTickets = SupportTicket::query()
            ->where('status', 'open')
            ->count();

        $pendingTickets = SupportTicket::where('status', 'pendiente')->count();

        $pendingRoleRequests = $users->where('role_request_status', 'pending')->count();

        $pendingAppointments = Appointment::where('status', 'pendiente')->count();

        $adoptionDocuments = collect($this->adoptions->list())->values();
        $publisherIds = $adoptionDocuments
            ->pluck('createdBy')
            ->filter(fn($id) => !empty($id))
            ->unique()
            ->values()
            ->all();

        $publishers = $users->whereIn('id', $publisherIds)->keyBy('id');

        $eligibleAdoptions = $adoptionDocuments->filter(function (array $adoption) use ($publishers): bool {
            $publisher = $publishers->get((int) ($adoption['createdBy'] ?? 0));

            return $publisher
                ? $publisher->hasAnyRole('veterinario', 'refugio')
                : false;
        })->values();

        $visibleAdoptionsCount = $eligibleAdoptions->filter(function (array $adoption): bool {
            return ! $this->isHidden($adoption);
        })->count();

        $hiddenAdoptionsCount = $eligibleAdoptions->filter(function (array $adoption): bool {
            return $this->isHidden($adoption);
        })->count();

        return view('admin.dashboard', compact(
            'pendingTickets',
            'pendingRoleRequests',
            'pendingAppointments',
            'usersCount',
            'pendingUsers',
            'openTickets',
            'visibleAdoptionsCount',
            'hiddenAdoptionsCount',
        ));
    }

    protected function isHidden(array $adoption): bool
    {
        $value = $adoption['isHidden'] ?? false;

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'si', 'sí'], true);
    }
}
