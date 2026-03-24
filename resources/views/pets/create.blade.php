
@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-10 bg-slate-50">
	<div class="w-full max-w-lg bg-white rounded-2xl shadow-xl p-8 sm:p-10 border border-gray-100">
		<h2 class="text-2xl font-bold text-center text-teal-700 mb-2">Registrar mascota</h2>
		<p class="text-center text-gray-600 mb-6">
			Registra a tu mascota para gestionar citas y servicios veterinarios fácilmente en Dejando Huella.
		</p>


		@if(session('success'))
			<div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">
				{{ session('success') }}
			</div>
		@endif

		@if(session('warning'))
			<div class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-yellow-700">
				{{ session('warning') }}
			</div>
		@endif

		@if(session('error'))
			<div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
				{{ session('error') }}
			</div>
		@endif

		<form method="POST" action="{{ route('my.pets.store') }}" enctype="multipart/form-data" class="space-y-8">
			@csrf

			<!-- Información básica -->
			<div class="space-y-4">
				<h3 class="text-lg font-semibold text-teal-700 mb-2">Información básica</h3>
				<div>
					<label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre de la mascota</label>
					<input type="text" name="name" id="name"
						class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition"
						placeholder="Ej. Juancho" value="{{ old('name') }}" required maxlength="100">
					@error('name')
						<p class="text-sm text-red-600 mt-1">{{ $message }}</p>
					@enderror
				</div>
				<div>
					<label for="species" class="block text-sm font-medium text-gray-700 mb-1">Especie</label>
					<select name="species" id="species"
						class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition"
						required>
						<option value="" disabled selected>Selecciona una especie</option>
						<option value="perro" {{ old('species') == 'perro' ? 'selected' : '' }}>Perro</option>
						<option value="gato" {{ old('species') == 'gato' ? 'selected' : '' }}>Gato</option>
						<option value="otro" {{ old('species') == 'otro' ? 'selected' : '' }}>Otro</option>
					</select>
					@error('species')
						<p class="text-sm text-red-600 mt-1">{{ $message }}</p>
					@enderror
				</div>
				<div>
					<label for="sex" class="block text-sm font-medium text-gray-700 mb-1">Sexo</label>
					<select name="sex" id="sex"
						class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition"
						required>
						<option value="" disabled selected>Selecciona el sexo</option>
						<option value="macho" {{ old('sex') == 'macho' ? 'selected' : '' }}>Macho</option>
						<option value="hembra" {{ old('sex') == 'hembra' ? 'selected' : '' }}>Hembra</option>
					</select>
					@error('sex')
						<p class="text-sm text-red-600 mt-1">{{ $message }}</p>
					@enderror
				</div>
			</div>

			<!-- Características -->
			<div class="space-y-4">
				<h3 class="text-lg font-semibold text-teal-700 mb-2">Características</h3>
				<div>
					<label for="breed" class="block text-sm font-medium text-gray-700 mb-1">Raza</label>
					<input type="text" name="breed" id="breed"
						class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition"
						placeholder="Ej. Bulldog francés" value="{{ old('breed') }}" maxlength="50">
					@error('breed')
						<p class="text-sm text-red-600 mt-1">{{ $message }}</p>
					@enderror
				</div>
				<div>
					<label for="ageYears" class="block text-sm font-medium text-gray-700 mb-1">Edad (años)</label>
					<input type="number" name="ageYears" id="ageYears" min="0" max="30"
						class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition"
						placeholder="Ej. 2" value="{{ old('ageYears') }}">
					@error('ageYears')
						<p class="text-sm text-red-600 mt-1">{{ $message }}</p>
					@enderror
				</div>
			</div>

			<!-- Foto de la mascota -->
			<div class="space-y-4">
				<h3 class="text-lg font-semibold text-teal-700 mb-2">Foto de la mascota <span class="text-xs text-gray-400 font-normal">(opcional)</span></h3>
				<div>
					<label for="photo" class="block text-sm font-medium text-gray-700 mb-1">Foto</label>
					<input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp" class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition bg-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700" onchange="previewPetPhoto(event)">
					@error('photo')
						<p class="text-sm text-red-600 mt-1">{{ $message }}</p>
					@enderror
					<div id="photo-preview-container" class="mt-3 hidden">
						<img id="photo-preview" src="#" alt="Vista previa" class="w-32 h-32 object-cover rounded-xl border border-teal-200 shadow">
					</div>
					<script>
						function previewPetPhoto(event) {
							const input = event.target;
							const preview = document.getElementById('photo-preview');
							const container = document.getElementById('photo-preview-container');
							if (input.files && input.files[0]) {
								const reader = new FileReader();
								reader.onload = function(e) {
									preview.src = e.target.result;
									container.classList.remove('hidden');
								};
								reader.readAsDataURL(input.files[0]);
							} else {
								preview.src = '#';
								container.classList.add('hidden');
							}
						}
					</script>
				</div>
			</div>

			<!-- Información adicional -->
			<div class="space-y-4">
				<h3 class="text-lg font-semibold text-teal-700 mb-2">Información adicional</h3>
				<div>
					<label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
					<textarea name="notes" id="notes" rows="3"
						class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition"
						placeholder="Ej. Es muy nervioso con extraños, requiere dieta especial, antecedentes médicos, etc." maxlength="255">{{ old('notes') }}</textarea>
					@error('notes')
						<p class="text-sm text-red-600 mt-1">{{ $message }}</p>
					@enderror
				</div>
			</div>

			<div class="flex flex-col sm:flex-row gap-3 pt-2">
				<button type="submit"
					class="w-full sm:w-auto bg-teal-600 text-white text-lg font-semibold py-3 px-6 rounded-xl shadow hover:bg-teal-700 transition focus:outline-none focus:ring-2 focus:ring-teal-400 focus:ring-offset-2">
					Registrar mascota
				</button>
				<a href="{{ route('my.pets') }}"
					class="w-full sm:w-auto text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 text-lg font-semibold py-3 px-6 rounded-xl transition text-center">
					Cancelar
				</a>
			</div>
		</form>
	</div>
</div>
@endsection
