<x-app-layout>
<h2 class="text-xl font-bold mb-4">Registrar clínica</h2>

<form method="POST" action="{{ route('vet.clinics.store') }}" class="space-y-3">
@csrf
<input class="border rounded p-2 w-full" name="name" placeholder="Nombre" required>
<input class="border rounded p-2 w-full" name="address_line" placeholder="Dirección" required>
<input class="border rounded p-2 w-full" name="neighborhood" placeholder="Colonia (opcional)">
<input class="border rounded p-2 w-full" name="city" value="Durango" required>
<input class="border rounded p-2 w-full" name="state" value="Durango" required>
<input class="border rounded p-2 w-full" name="lat" placeholder="Lat (opcional)">
<input class="border rounded p-2 w-full" name="lng" placeholder="Lng (opcional)">
<button class="border rounded px-4 py-2">Guardar</button>
</form>

</x-app-layout>
