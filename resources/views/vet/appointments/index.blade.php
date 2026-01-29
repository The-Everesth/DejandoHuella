<x-app-layout>
<h2 class="text-xl font-bold mb-4">Citas de mis clínicas</h2>

<div class="space-y-3">
@forelse($appointments as $a)
  <div class="p-4 border rounded">
    <div class="font-semibold">{{ $a->service->name }} — {{ $a->pet->name }}</div>
    <div class="text-sm text-gray-600">
      Clínica: {{ $a->clinic->name }} | {{ $a->scheduled_at->format('Y-m-d H:i') }}
    </div>
    <div class="text-sm">Cliente: {{ $a->owner->name }} ({{ $a->owner->email }})</div>
    <div>Estado: <b>{{ $a->status }}</b></div>

    <div class="mt-3 flex gap-3">
      <form method="POST" action="{{ route('vet.appointments.status', $a) }}">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="confirmada">
        <button class="underline">Confirmar</button>
      </form>
      <form method="POST" action="{{ route('vet.appointments.status', $a) }}">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="atendida">
        <button class="underline">Atendida</button>
      </form>
      <form method="POST" action="{{ route('vet.appointments.status', $a) }}">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="cancelada">
        <button class="underline">Cancelar</button>
      </form>
    </div>
  </div>
@empty
  <p>No hay citas.</p>
@endforelse
</div>

</x-app-layout>
