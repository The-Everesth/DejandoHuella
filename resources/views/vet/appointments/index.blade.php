<x-app-layout>
<h2 class="text-xl font-bold mb-4">Citas de mis clínicas</h2>

<div class="space-y-3">
@forelse($appointments as $a)
  <div class="p-4 border rounded">
    <div class="font-semibold">Servicio: {{ $a->serviceId ?? '-' }} — Mascota: {{ $a->petId ?? '-' }}</div>
    <div class="text-sm text-gray-600">
      Clínica: {{ $a->clinicId ?? '-' }} | {{ $a->startAt ?? '-' }}
    </div>
    <div class="text-sm">Cliente: {{ $a->userUid ?? '-' }} | Contacto: {{ $a->contact ?? '-' }}</div>
    <div>Estado: <b>{{ $a->status }}</b></div>
    @if(!empty($a->notes))
      <div class="text-xs text-gray-500">Notas usuario: {{ $a->notes }}</div>
    @endif
    @if(!empty($a->vetNotes))
      <div class="text-xs text-green-700">Notas vet: {{ $a->vetNotes }}</div>
    @endif

    @if($a->status === 'PENDING')
    <div class="mt-3">
      <form method="POST" action="{{ route('vet.appointments.status', $a->id) }}" class="flex flex-col gap-2">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="CONFIRMED">
        <label>Notas para el usuario (opcional):
          <textarea name="vetNotes" class="border rounded p-2 w-full" rows="2"></textarea>
        </label>
        <button class="border rounded px-3 py-1 bg-green-600 text-white">Confirmar</button>
      </form>
      <form method="POST" action="{{ route('vet.appointments.status', $a->id) }}" class="mt-2">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="REJECTED">
        <button class="border rounded px-3 py-1 bg-red-600 text-white">Rechazar</button>
      </form>
    </div>
    @endif
  </div>
@empty
  <p>No hay citas.</p>
@endforelse
</div>

</x-app-layout>
