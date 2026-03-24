@extends('layouts.app')

@section('content')
<h2 class="text-xl font-bold mb-4">Servicios de: {{ $clinic->name }}</h2>

<form method="POST" action="{{ route('vet.clinics.services.update', $clinic) }}" class="space-y-3">
@csrf

@foreach($services as $s)
    @php
        $attached = $clinic->services->contains($s->id);
        $price = $attached ? optional($clinic->services->find($s->id))->pivot->price : null;
    @endphp

    <div class="p-3 border rounded">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="services[]" value="{{ $s->id }}" {{ $attached ? 'checked' : '' }}>
            <span class="font-semibold">{{ $s->name }}</span>
        </label>

        <div class="mt-2">
            <input class="border rounded p-2 w-full" name="prices[{{ $s->id }}]" placeholder="Precio (opcional)" value="{{ $price }}">
        </div>
    </div>
@endforeach

<button class="border rounded px-4 py-2">Guardar</button>
</form>

@endsection
