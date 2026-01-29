<x-app-layout>
    <x-page-title title="Editar usuario" subtitle="Actualiza nombre y correo." />

    <x-card>
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="font-bold text-gray-800">Nombre</label>
                <input class="mt-2 w-full border rounded-xl p-3" name="name" value="{{ old('name', $user->name) }}" required>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <label class="font-bold text-gray-800">Correo</label>
                <input class="mt-2 w-full border rounded-xl p-3" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex gap-2">
                <x-button type="submit" variant="primary">Guardar</x-button>
                <a href="{{ route('admin.users.index') }}"><x-button variant="outline">Volver</x-button></a>
            </div>
        </form>
    </x-card>
</x-app-layout>
