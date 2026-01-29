<x-app-layout>
<form method="POST" enctype="multipart/form-data" action="{{ route('pets.store') }}">
@csrf
<input name="name" placeholder="Nombre">
<input name="species" placeholder="Especie">
<input name="sex" placeholder="Sexo">
<input name="photo" type="file">
<button>Guardar</button>
</form>
</x-app-layout>
