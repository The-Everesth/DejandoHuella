<x-app-layout>
<h2 class="text-xl font-bold mb-4">Servicios Médicos</h2>

<div class="space-y-3">
@foreach($services as $s)
  <div class="p-4 border rounded">
    <div class="font-semibold">{{ $s->name }}</div>
    <div class="text-sm text-gray-600">{{ $s->description }}</div>
    <a class="underline" href="{{ route('services.clinics', $s) }}">Ver clínicas</a>
  </div>
@endforeach
</div>

</x-app-layout>
