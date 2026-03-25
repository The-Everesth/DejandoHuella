@php
    $role = $mainRole ?? null;
    if (!isset($recentAdoptions)) {
        $recentAdoptions = collect();
    }
    if (!isset($recentRequests)) {
        $recentRequests = collect();
    }
@endphp
@if($role === 'veterinario')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <div class="font-semibold text-gray-600 mb-2">Actividad médica</div>
            </div>
            <div>
                <div class="font-semibold text-gray-600 mb-1">Citas pendientes de confirmar/rechazar</div>
                @forelse($recentAppointments as $appt)
                    @php
                        $statusMap = [
                            'pending' => ['label' => 'Pendiente', 'color' => 'yellow'],
                            'pendiente' => ['label' => 'Pendiente', 'color' => 'yellow'],
                        ];
                        $status = strtolower($appt['status'] ?? '');
                        $badge = $statusMap[$status] ?? ['label' => ucfirst($status), 'color' => 'gray'];
                        $petName = $appt['pet_name'] ?? $appt['petName'] ?? null;
                        $ownerName = $appt['owner_name'] ?? $appt['ownerName'] ?? null;
                        $reason = $appt['reason'] ?? $appt['motivo'] ?? null;
                        $summary = collect([
                            $petName ? 'Mascota: '.$petName : null,
                            $ownerName ? 'Dueño: '.$ownerName : null,
                            $reason ? 'Motivo: '.$reason : null,
                        ])->filter()->implode(' | ');
                    @endphp
                    @include('dashboard.partials.activity-item', [
                        'title' => $appt['service_name'] ?? $appt['serviceId'] ?? 'Cita',
                        'subtitle' => $summary,
                        'date' => $appt['startAt'] ? date('d/m/Y H:i', strtotime($appt['startAt'])) : null,
                        'status' => $status,
                        'statusLabel' => $badge['label'],
                        'statusColor' => $badge['color'],
                        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-7 4h4"/></svg>',
                    ])
                @empty
                    <div class="flex flex-col items-center justify-center bg-slate-50 rounded-lg p-6 text-gray-400">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-7 4h4"/></svg>
                        <div class="font-semibold">Sin citas pendientes</div>
                        <div class="text-xs">No hay citas pendientes de confirmar o rechazar.</div>
                    </div>
                @endforelse
            </div>
            <div>
                <div class="font-semibold text-gray-600 mb-1">Solicitudes de adopción recibidas</div>
                @forelse($recentRequests as $req)
                    @php
                        $statusMap = [
                            'pending' => ['label' => 'Nueva solicitud', 'color' => 'yellow'],
                            'pendiente' => ['label' => 'Nueva solicitud', 'color' => 'yellow'],
                        ];
                        $status = strtolower($req['status'] ?? '');
                        $badge = $statusMap[$status] ?? ['label' => ucfirst($status), 'color' => 'gray'];
                        $petName = $req['petName'] ?? null;
                        $applicantName = $req['applicantName'] ?? null;
                        $message = $req['message'] ?? null;
                        $summary = collect([
                            $petName ? 'Mascota: '.$petName : null,
                            $applicantName ? 'Solicitante: '.$applicantName : null,
                            $message ? 'Mensaje: '.$message : null,
                        ])->filter()->implode(' | ');
                    @endphp
                    @include('dashboard.partials.activity-item', [
                        'title' => 'Solicitud de adopción',
                        'subtitle' => $summary,
                        'date' => isset($req['createdAt']) && $req['createdAt'] ? date('d/m/Y', strtotime($req['createdAt'])) : null,
                        'status' => $status,
                        'statusLabel' => $badge['label'],
                        'statusColor' => $badge['color'],
                        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
                    ])
                @empty
                    <div class="flex flex-col items-center justify-center bg-slate-50 rounded-lg p-6 text-gray-400">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
                        <div class="font-semibold">Sin solicitudes pendientes</div>
                        <div class="text-xs">Cuando recibas solicitudes pendientes aparecerán aquí.</div>
                    </div>
                @endforelse
            </div>
        </div>
        </div>
    @endif
            </div>
        {{-- cierre correcto de grid y div principal --}}
    </div>
</div>