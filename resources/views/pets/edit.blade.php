<x-app-layout>
<form method="POST" action="{{ route('pets.update',$pet) }}">
@csrf @method('PUT')
<input name="name" value="{{ $pet->name }}">
<input name="species" value="{{ $pet->species }}">
<input name="sex" value="{{ $pet->sex }}">
<button>Actualizar</button>
</form>
</x-app-layout>
