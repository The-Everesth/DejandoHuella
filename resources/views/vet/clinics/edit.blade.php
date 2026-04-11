@extends('layouts.app')

@section('content')
    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Editar Clínica: {{ $clinic['name'] ?? '' }}</h2>

                <form method="POST" action="{{ route('vet.clinics.update', $clinic['id'] ?? '') }}" class="space-y-4">
                    @csrf @method('PUT')

                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la clínica *</label>
                        <input type="text" name="name" value="{{ old('name', $clinic['name'] ?? '') }}" placeholder="Ej: Clínica Veterinaria San Carlos" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('name') border-red-500 @enderror">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone', $clinic['phone'] ?? '') }}" placeholder="Ej: 618-123-4567" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('phone') border-red-500 @enderror">
                        @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $clinic['email'] ?? '') }}" placeholder="Ej: contacto@clinica.com" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('email') border-red-500 @enderror">
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Dirección -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <input type="text" name="address" value="{{ old('address', $clinic['address'] ?? '') }}" placeholder="Ej: Calle Principal 123, Durango" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('address') border-red-500 @enderror">
                        @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción de la clínica</label>
                        <textarea name="description" placeholder="Cuéntanos sobre tu clínica, especialidades, equipo, etc." rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('description') border-red-500 @enderror">{{ old('description', $clinic['description'] ?? '') }}</textarea>
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Horario -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Horario de atención</label>
                        <input type="text" name="opening_hours" value="{{ old('opening_hours', $clinic['opening_hours'] ?? '') }}" placeholder="Ej: Lun-Vie 9am-6pm, Sábado 10am-2pm" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('opening_hours') border-red-500 @enderror">
                        @error('opening_hours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Website -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sitio web</label>
                        <input type="url" name="website" value="{{ old('website', $clinic['website'] ?? '') }}" placeholder="Ej: https://www.miclinica.com" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('website') border-red-500 @enderror">
                        @error('website') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Publicar -->
                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="is_public" id="is_public" value="1" {{ old('is_public', $clinic['is_public'] ?? false) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                        <label for="is_public" class="text-sm font-medium text-gray-700">Publicar esta clínica (visible para pacientes)</label>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3 pt-6 border-t">
                        <a href="{{ route('vet.clinics.index') }}" class="flex-1 text-center bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition font-medium">
                            Cancelar
                        </a>
                        <button type="submit" class="flex-1 bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition font-medium">
                            Actualizar Clínica
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
