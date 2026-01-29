<x-app-layout>
<h2 class="text-xl font-bold mb-4">Mis citas</h2>

<div class="space-y-3">
@forelse($appointments as $a)
  <div class="p-4 border rounded">
    <div class="font-semibold">{{ $a->service->name }} — {{ $a->pet->name }}</div>
    <div class="text-sm text-gray-600">{{ $a->clinic->name }} | {{ $a->scheduled_at->format('Y-m-d H:i') }}</div>
    <div>Estado: <b>{{ $a->status }}</b></div>
  </div>
@empty
  <p>No tienes citas.</p>
@endforelse
</div>

</x-app-layout>
