<x-app-layout>
<h2 class="text-xl font-bold mb-4">Editar servicio</h2>

<form method="POST" action="{{ route('admin.services.update', $service) }}" class="space-y-3">
@csrf @method('PUT')
<input class="border rounded p-2 w-full" name="name" value="{{ $service->name }}" required>
<textarea class="border rounded p-2 w-full" name="description">{{ $service->description }}</textarea>
<input class="border rounded p-2 w-full" name="base_price" value="{{ $service->base_price }}">
<input class="border rounded p-2 w-full" name="duration_minutes" value="{{ $service->duration_minutes }}">
<button class="border rounded px-4 py-2">Actualizar</button>
</form>

</x-app-layout>
