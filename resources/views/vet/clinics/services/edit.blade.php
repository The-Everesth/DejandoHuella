<x-app-layout>
    <x-page-title title="Servicios de {{ $clinic->name }}" subtitle="Selecciona los servicios que ofreces." />

    <x-card>
        <form method="POST" action="{{ route('vet.clinics.services.update', ['clinic' => $clinicId]) }}" class="space-y-6">
            @csrf

            <div>
                <div class="font-extrabold text-slate-900 mb-2">Servicios disponibles</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($services as $s)
                        <div class="p-3 rounded-2xl border hover:bg-slate-50">
                            <label class="flex items-center gap-3 font-bold text-slate-800">
                                <input type="checkbox" name="service_ids[]" value="{{ $s['id'] }}" @checked(in_array($s['id'], $selectedServiceIds ?? []))>
                                {{ $s['name'] }}
                                <span class="ml-2 text-xs text-slate-500">({{ $s['durationMinutes'] ?? '-' }} min)</span>
                            </label>
                        </div>
                    @endforeach

                </div>
            </div>

            <div class="flex gap-2">
                <x-button type="submit" variant="soft">Guardar</x-button>
                <a href="{{ route('vet.clinics.index') }}">
                    <x-button variant="outline">Volver</x-button>
                </a>
            </div>
        </form>
    </x-card>
</x-app-layout>
