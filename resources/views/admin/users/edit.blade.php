
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-2">
    <div class="max-w-lg mx-auto">
        <!-- Encabezado -->
        <div class="mb-8 text-center">
            <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-1">Editar usuario</h2>
            <p class="text-gray-600 text-sm">Actualiza nombre y correo.</p>
        </div>

        <x-card padding="p-8">
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="space-y-7">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block font-semibold text-gray-700 mb-2">Nombre</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required
                        class="block w-full rounded-lg border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 p-3 text-gray-800 bg-gray-50 shadow-sm transition" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <label for="email" class="block font-semibold text-gray-700 mb-2">Correo</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                        class="block w-full rounded-lg border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 p-3 text-gray-800 bg-gray-50 shadow-sm transition" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="pt-4 mt-6 border-t border-gray-100 flex flex-col sm:flex-row gap-3">
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
        </x-card>
    </div>
</div>
@endsection
