<x-app-layout>
    <x-page-title
        title="Mis solicitudes de adopcion"
        subtitle="Aqui puedes ver el estado de tus solicitudes de adopcion."
    />

    <div class="space-y-3">
        @forelse($requests as $item)
            @php
                $status = strtolower((string) ($item['status'] ?? 'pendiente'));
                $isApproved = in_array($status, ['aprobada', 'approved'], true);
                $statusLabel = $isApproved ? 'Aprobada' : 'Pendiente';
                $statusClasses = $isApproved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
            @endphp

            <div class="p-4 border rounded bg-white">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div class="font-semibold">
                        Mascota: {{ $item['petName'] ?? 'Sin nombre' }}
                    </div>

                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $statusClasses }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>
        @empty
            <div class="p-4 border rounded bg-white text-gray-600">
                Aun no has realizado solicitudes de adopcion.
            </div>
        @endforelse
    </div>
</x-app-layout>
