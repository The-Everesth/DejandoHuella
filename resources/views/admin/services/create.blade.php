<x-app-layout>
<h2 class="text-xl font-bold mb-4">Crear servicio</h2>

<form method="POST" action="{{ route('admin.services.store') }}" class="space-y-3">
@csrf
<input class="border rounded p-2 w-full" name="name" placeholder="Nombre" required>
<textarea class="border rounded p-2 w-full" name="description" placeholder="Descripción"></textarea>
<input class="border rounded p-2 w-full" name="base_price" placeholder="Precio base (opcional)">
<input class="border rounded p-2 w-full" name="duration_minutes" placeholder="Duración (min) opcional">
<button class="border rounded px-4 py-2">Guardar</button>
</form>

</x-app-layout>
