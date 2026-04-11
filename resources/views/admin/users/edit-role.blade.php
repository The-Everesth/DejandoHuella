
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-2">
    <div class="max-w-lg mx-auto">
        <!-- Encabezado -->
        <div class="mb-8 text-center">
            <h2 class="text-2xl md:text-3xl font-extrabold text-gray-800 mb-1">Cambio de rol de usuario</h2>
            <p class="text-gray-500 text-sm">El cambio de rol afecta los permisos y accesos del usuario en el sistema.</p>
        </div>

        <!-- Card usuario -->
        <div class="bg-white rounded-2xl shadow p-6 mb-8">
            <div class="mb-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <div class="font-semibold text-gray-700 text-lg">{{ $user->name }}</div>
                    <div class="text-gray-500 text-sm">{{ $user->email }}</div>
                </div>
                <div>
                    @php
                        $roleColors = [
                            'admin' => 'bg-blue-100 text-blue-800 border border-blue-200',
                            'superadmin' => 'bg-purple-100 text-purple-800 border border-purple-200',
                            'user' => 'bg-gray-100 text-gray-700 border border-gray-200',
                            'moderador' => 'bg-green-100 text-green-800 border border-green-200',
                        ];
                        $role = strtolower($currentRole ?? 'sin rol');
                        $badgeClass = $roleColors[$role] ?? 'bg-gray-100 text-gray-600 border border-gray-200';
                    @endphp
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                        {{ ucfirst($currentRole ?? 'sin rol') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Errores -->
        @if($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ route('admin.users.role.update', $user->id ?? $user->_docId ?? $user['id'] ?? $user['docId']) }}" class="bg-white rounded-2xl shadow p-6 space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="role" class="block font-semibold text-gray-700 mb-2">Nuevo rol</label>
                <select id="role" name="role"
                    class="block w-full rounded-lg border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 p-3 text-gray-800 bg-gray-50 shadow-sm transition"
                    required>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ $currentRole === $r->name ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-2">Selecciona el nuevo rol que tendrá el usuario.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 mt-4">
                <button type="submit"
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow transition focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
                    Guardar
                </button>
                <a href="{{ route('admin.users.index') }}"
                    class="inline-block bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-2 rounded-lg border border-gray-200 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 text-center">
                    Volver
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
