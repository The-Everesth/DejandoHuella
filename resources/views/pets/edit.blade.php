
<x-app-layout>
	<x-page-title title="Editar mascota" />
	<div class="max-w-lg mx-auto">
		@if($errors->any())
			<div class="mb-4 text-red-700 bg-red-100 rounded p-2">
				<ul class="list-disc pl-5">
					@foreach($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif
			<!-- Campos duplicados eliminados, solo queda el formulario correcto abajo -->
		<form method="POST" action="{{ route('my.pets.update', ['pet' => $pet->id]) }}" class="space-y-4">
			@csrf
			@method('PATCH')
			<div>
				<label class="block font-bold">Nombre</label>
				<input name="name" class="input w-full" required maxlength="100" value="{{ old('name', $pet->name) }}">
			</div>
			<div>
				<label class="block font-bold">Especie</label>
				<select name="species" class="input w-full" required>
					<option value="">Selecciona...</option>
					<option value="perro" @selected(old('species', $pet->species)=='perro')>Perro</option>
					<option value="gato" @selected(old('species', $pet->species)=='gato')>Gato</option>
					<option value="otro" @selected(old('species', $pet->species)=='otro')>Otro</option>
				</select>
			</div>
			<div>
				<label class="block font-bold">Sexo</label>
				<select name="sex" class="input w-full" required>
					<option value="">Selecciona...</option>
					<option value="macho" @selected(old('sex', $pet->sex)=='macho')>Macho</option>
					<option value="hembra" @selected(old('sex', $pet->sex)=='hembra')>Hembra</option>
				</select>
			</div>
			<div>
				<label class="block font-bold">Raza</label>
				<input name="breed" class="input w-full" maxlength="50" value="{{ old('breed', $pet->breed ?? '') }}">
			</div>
			<div>
				<label class="block font-bold">Edad (años)</label>
				<input name="ageYears" type="number" min="0" max="50" class="input w-full" value="{{ old('ageYears', $pet->ageYears ?? '') }}">
			</div>
			<div>
				<label class="block font-bold">Notas</label>
				<textarea name="notes" class="input w-full" maxlength="255">{{ old('notes', $pet->notes ?? '') }}</textarea>
			</div>
			<div class="flex gap-2 mt-4">
				<x-button type="submit">Actualizar</x-button>
				<a href="{{ route('my.pets') }}"><x-button variant="outline">Cancelar</x-button></a>
			</div>
		</form>
	</div>
</x-app-layout>
