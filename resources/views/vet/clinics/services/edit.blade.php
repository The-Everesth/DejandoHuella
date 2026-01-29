<x-app-layout>
    <x-page-title title="Servicios de {{ $clinic->name }}" subtitle="Selecciona los servicios que ofreces." />

    <x-card>
        <form method="POST" action="{{ route('vet.clinics.services.update', $clinic) }}" class="space-y-6">
            @csrf

            <div>
                <div class="font-extrabold text-slate-900 mb-2">Servicios disponibles</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($services as $s)
                        @php
                            $pivot = $clinic->services->firstWhere('id', $s->id)?->pivot;
                            $checked = !is_null($pivot);
                        @endphp

                        <div class="p-3 rounded-2xl border hover:bg-slate-50">
                            <label class="flex items-center gap-3 font-bold text-slate-800">
                            <input type="checkbox" name="services[{{ $s->id }}][enabled]" @checked($checked)>
                            {{ $s->name }}
                            </label>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                            <div>
                                <label class="text-xs font-bold text-slate-600">Precio (MXN)</label>
                                <input class="mt-1 w-full border rounded-xl p-2"
                                    name="services[{{ $s->id }}][price]"
                                    value="{{ $pivot?->price }}"
                                    placeholder="Ej: 250">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-600">Duración (min)</label>
                                <input class="mt-1 w-full border rounded-xl p-2"
                                    name="services[{{ $s->id }}][duration_minutes]"
                                    value="{{ $pivot?->duration_minutes }}"
                                    placeholder="Ej: 30">
                            </div>
                            </div>
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
