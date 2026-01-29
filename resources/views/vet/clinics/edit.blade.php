<x-app-layout>
<h2 class="text-xl font-bold mb-4">Editar clínica</h2>

<form method="POST" action="{{ route('vet.clinics.update', $clinic) }}" class="space-y-3">
@csrf @method('PUT')
<input class="border rounded p-2 w-full" name="name" value="{{ $clinic->name }}" required>
<input class="border rounded p-2 w-full" name="address_line" value="{{ $clinic->address_line }}" required>
<input class="border rounded p-2 w-full" name="neighborhood" value="{{ $clinic->neighborhood }}">
<input class="border rounded p-2 w-full" name="city" value="{{ $clinic->city }}" required>
<input class="border rounded p-2 w-full" name="state" value="{{ $clinic->state }}" required>
<input class="border rounded p-2 w-full" name="lat" value="{{ $clinic->lat }}">
<input class="border rounded p-2 w-full" name="lng" value="{{ $clinic->lng }}">
<button class="border rounded px-4 py-2">Actualizar</button>
</form>

</x-app-layout>
