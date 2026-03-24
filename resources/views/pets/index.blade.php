
@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-6 min-h-[80vh]">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Mis mascotas</h1>
        <a href="{{ route('my.pets.create') }}"
           class="inline-flex items-center gap-2 bg-teal-600 text-white px-5 py-3 rounded-xl shadow hover:bg-teal-700 transition text-lg font-semibold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Agregar mascota
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 text-green-700 bg-green-100 rounded p-3 shadow">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 text-red-700 bg-red-100 rounded p-3 shadow">{{ session('error') }}</div>
    @endif

    @if(count($pets))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($pets as $pet)
                <div class="group bg-white rounded-2xl shadow-lg p-6 flex flex-col gap-3 transition hover:shadow-2xl hover:-translate-y-1 duration-200 relative">
                    <!-- Avatar/placeholder -->
                    <div class="flex items-center gap-4 mb-2">
                        <div class="flex-shrink-0 w-14 h-14 rounded-full bg-teal-100 flex items-center justify-center text-2xl font-bold text-teal-700 border border-teal-200 overflow-hidden">
                            @if(!empty($pet['photoUrl']))
                                <img src="{{ $pet['photoUrl'] }}" alt="Foto de {{ $pet['name'] }}" class="w-14 h-14 rounded-full object-cover">
                            @else
                                <svg class="w-8 h-8 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="text-xl font-bold text-gray-900 leading-tight">{{ $pet['name'] }}</div>
                            <div class="flex gap-2 mt-1">
                                <span class="inline-block px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 text-xs font-semibold border border-teal-200">{{ ucfirst($pet['species']) }}</span>
                                <span class="inline-block px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">{{ ucfirst($pet['sex']) }}</span>
                            </div>
                        </div>
                        <!-- Menú de acciones -->
                        <div class="relative">
                            <button type="button" class="p-2 rounded-full hover:bg-slate-100 focus:outline-none group-hover:bg-slate-100 transition" onclick="this.nextElementSibling.classList.toggle('hidden')">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="1.5"/><circle cx="19.5" cy="12" r="1.5"/><circle cx="4.5" cy="12" r="1.5"/></svg>
                            </button>
                            <div class="hidden absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded shadow-lg z-10">
                                <a href="{{ route('my.pets.edit', $pet['id']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-teal-50">Editar</a>
                                <form method="POST" action="{{ route('my.pets.destroy', $pet['id']) }}" onsubmit="return confirm('¿Eliminar mascota?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        @if(!empty($pet['breed']))<div class="text-sm text-gray-700"><span class="font-medium">Raza:</span> {{ $pet['breed'] }}</div>@endif
                        @if(!empty($pet['ageYears']))<div class="text-sm text-gray-700"><span class="font-medium">Edad:</span> {{ $pet['ageYears'] }} años</div>@endif
                        @if(!empty($pet['notes']))<div class="text-xs text-gray-500 italic border-l-4 border-teal-100 pl-2 mt-1">{{ $pet['notes'] }}</div>@endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-20">
            <svg class="w-16 h-16 text-teal-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
            <div class="text-gray-500 text-lg">No tienes mascotas registradas.</div>
        </div>
    @endif
</div>
@endsection
