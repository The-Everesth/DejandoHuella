<x-app-layout>
<h2>Mis Mascotas</h2>
<a href="{{ route('pets.create') }}">Registrar Mascota</a>

@foreach($pets as $pet)
    <div>
        <b>{{ $pet->name }}</b> ({{ $pet->species }})
        <a href="{{ route('pets.edit',$pet) }}">Editar</a>
        <form method="POST" action="{{ route('pets.destroy',$pet) }}">
            @csrf @method('DELETE')
            <button>Eliminar</button>
        </form>
    </div>
@endforeach
</x-app-layout>
