<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AdoptionRequest;

class AdminDashboardController extends Controller
{
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

        $pendingAdoptionRequests = AdoptionRequest::where('status', 'pendiente')->count();

        return view('admin.dashboard', compact(
            'pendingTickets',
            'pendingRoleRequests',
            'pendingAppointments',
            'pendingAdoptionRequests',
            'usersCount',
            'pendingUsers',
            'openTickets',
            
        ));
    }
}
