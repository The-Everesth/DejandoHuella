

@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-10 bg-slate-50">
	<div class="w-full max-w-lg bg-white rounded-2xl shadow-xl p-8 sm:p-10 border border-gray-100">
		<h2 class="text-2xl font-bold text-center text-teal-700 mb-2">Editar mascota</h2>
		<p class="text-center text-gray-600 mb-6">
			Actualiza la información de tu mascota y su foto.
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

		@if($errors->any())
			<div class="mb-4 text-red-700 bg-red-100 rounded p-2">
				<ul class="list-disc pl-5">
					@foreach($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		<form method="POST" action="{{ route('my.pets.update', ['pet' => $pet->id]) }}" enctype="multipart/form-data" class="space-y-8">
			@csrf
			@method('PATCH')

			<!-- Información básica -->
			<div class="space-y-4">
				<h3 class="text-lg font-semibold text-teal-700 mb-2">Información básica</h3>
				<div>
					<x-input-label for="name" value="Nombre de la mascota" />
					<input type="text" name="name" id="name"
						class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition"
						placeholder="Ej. Juancho" value="{{ old('name', $pet->name) }}" required maxlength="100">
					<x-input-error :messages="$errors->get('name')" />
				</div>
				<div>
					<x-input-label for="species" value="Especie" />
					<select name="species" id="species"
						class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition"
						required>
						<option value="" disabled>Selecciona una especie</option>
						<option value="perro" {{ old('species', $pet->species)=='perro' ? 'selected' : '' }}>Perro</option>
						<option value="gato" {{ old('species', $pet->species)=='gato' ? 'selected' : '' }}>Gato</option>
						<option value="otro" {{ old('species', $pet->species)=='otro' ? 'selected' : '' }}>Otro</option>
					</select>
					<x-input-error :messages="$errors->get('species')" />
				</div>
				<div>
					<x-input-label for="sex" value="Sexo" />
					<select name="sex" id="sex"
						class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition"
						required>
						<option value="" disabled>Selecciona el sexo</option>
						<option value="macho" {{ old('sex', $pet->sex)=='macho' ? 'selected' : '' }}>Macho</option>
						<option value="hembra" {{ old('sex', $pet->sex)=='hembra' ? 'selected' : '' }}>Hembra</option>
					</select>
					<x-input-error :messages="$errors->get('sex')" />
				</div>
			</div>

			<!-- Características -->
			<div class="space-y-4">
				<h3 class="text-lg font-semibold text-teal-700 mb-2">Características</h3>
				<div>
					<x-input-label for="breed" value="Raza" />
					<input type="text" name="breed" id="breed"
						class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition"
						placeholder="Ej. Bulldog francés" value="{{ old('breed', $pet->breed ?? '') }}" maxlength="50">
					<x-input-error :messages="$errors->get('breed')" />
				</div>
				<div>
					<x-input-label for="ageYears" value="Edad (años)" />
					<input type="number" name="ageYears" id="ageYears" min="0" max="30"
						class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition"
						placeholder="Ej. 2" value="{{ old('ageYears', $pet->ageYears ?? '') }}">
					<x-input-error :messages="$errors->get('ageYears')" />
				</div>
			</div>

			<!-- Foto de la mascota -->
			<div class="space-y-4">
				<h3 class="text-lg font-semibold text-teal-700 mb-2">Foto de la mascota <span class="text-xs text-gray-400 font-normal">(opcional)</span></h3>
				<div>
					<x-input-label for="photo" value="Foto" />
					<input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp"
						class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition bg-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700"
						onchange="previewPetPhoto(event)">
					<x-input-error :messages="$errors->get('photo')" />

					<!-- Vista previa de la foto actual o nueva -->
					<div id="photo-preview-container" class="mt-3 {{ (property_exists($pet, 'photoUrl') && $pet->photoUrl) || old('photo') ? '' : 'hidden' }}">
						<img id="photo-preview" src="{{ (property_exists($pet, 'photoUrl') && $pet->photoUrl) ? $pet->photoUrl : '#' }}" alt="Foto actual" class="w-32 h-32 object-cover rounded-xl border border-teal-200 shadow">
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
							// Fallback a la foto actual si existe, si no, placeholder
							preview.src = "{{ (property_exists($pet, 'photoUrl') && $pet->photoUrl) ? $pet->photoUrl : '#' }}";
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
					<x-input-label for="notes" value="Notas" />
					<textarea name="notes" id="notes" rows="3"
						class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition"
						placeholder="Ej. Es muy nervioso con extraños, requiere dieta especial, antecedentes médicos, etc." maxlength="255">{{ old('notes', $pet->notes ?? '') }}</textarea>
					<x-input-error :messages="$errors->get('notes')" />
				</div>
			</div>

			<div class="flex flex-col sm:flex-row gap-3 pt-2">
				<x-button type="submit">Actualizar mascota</x-button>
				<a href="{{ route('my.pets') }}" class="w-full sm:w-auto"><x-button variant="outline">Cancelar</x-button></a>
			</div>
		</form>
	</div>
</div>
@endsection
