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
        // Usuarios activos (sin desactivados)
        $usersCount = User::query()
            ->whereNull('deleted_at')
            ->count();

        // Solicitudes pendientes (rol veterinario/refugio)
        $pendingUsers = User::query()
            ->where('role_request_status', 'pending')
            ->whereIn('requested_role', ['veterinario', 'refugio'])
            ->count();

        // Tickets abiertos 
        $openTickets = SupportTicket::query()
            ->where('status', 'open')
            ->count();

        $pendingTickets = SupportTicket::where('status', 'pendiente')->count();

        $pendingRoleRequests = User::where('role_request_status', 'pending')->count();

        $pendingAppointments = Appointment::where('status', 'pendiente')->count();

        $adoptionDocuments = collect($this->adoptions->list())->values();
        $publisherIds = $adoptionDocuments
            ->pluck('createdBy')
            ->filter(function ($id): bool {
                return is_numeric($id) && (int) $id > 0;
            })
            ->map(function ($id): int {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();

        $publishers = User::withTrashed()
            ->whereIn('id', $publisherIds)
            ->get()
            ->keyBy('id');

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
