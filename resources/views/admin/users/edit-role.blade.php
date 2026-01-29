<x-app-layout>
    <h2 class="text-xl font-bold mb-4">Cambiar rol</h2>

    <div class="p-4 border rounded mb-4">
        <div><b>Usuario:</b> {{ $user->name }}</div>
        <div><b>Email:</b> {{ $user->email }}</div>
        <div><b>Rol actual:</b> {{ $currentRole ?? 'sin rol' }}</div>
    </div>

    @if($errors->any())
        <div class="p-3 border rounded mb-3">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="space-y-3">
        @csrf
        @method('PUT')

        <div>
            <label class="block mb-1">Nuevo rol</label>
            <select name="role" class="border rounded p-2 w-full" required>
                @foreach($roles as $r)
                    <option value="{{ $r->name }}" {{ $currentRole === $r->name ? 'selected' : '' }}>
                        {{ $r->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="border rounded px-4 py-2">Guardar</button>
        <a class="underline ml-3" href="{{ route('admin.users.index') }}">Volver</a>
    </form>
</x-app-layout>
