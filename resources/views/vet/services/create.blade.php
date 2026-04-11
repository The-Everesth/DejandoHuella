@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto py-8">
    <div class="bg-white rounded-lg shadow p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-6">Nuevo servicio global</h1>
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('vet.services.store') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Nombre del servicio</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Descripción</label>
                <textarea name="description" rows="3" class="w-full border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
            </div>
            <div class="flex gap-4">
                <div class="flex-1">
                    <label class="block text-gray-700 font-semibold mb-1">Precio base (MXN)</label>
                    <input type="number" name="base_price" value="{{ old('base_price') }}" min="0" step="0.01" required class="w-full border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex-1">
                    <label class="block text-gray-700 font-semibold mb-1">Duración (minutos)</label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes') }}" min="1" required class="w-full border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="flex justify-end mt-6">
                <a href="{{ route('vet.services.index') }}" class="mr-4 text-gray-600 hover:underline">Cancelar</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded shadow">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
