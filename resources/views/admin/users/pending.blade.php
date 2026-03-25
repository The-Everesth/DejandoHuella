@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">Solicitudes de rol pendientes</h2>
        <a class="underline" href="{{ route('admin.users.index') }}">Volver a usuarios</a>
    </div>

    @if(session('success'))
        <div class="p-3 border rounded mb-3">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="p-3 border rounded mb-3">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    @endif

    <div class="space-y-3">
        @forelse($users as $u)
            <div class="p-4 border rounded">
                <div class="font-semibold">{{ $u->name }}</div>
                <div class="text-sm text-gray-600">{{ $u->email }}</div>

                <div class="mt-1">
                    Solicitó: <b>{{ $u->requested_role }}</b>
                    @if($u->role_requested_at)
                        <span class="text-sm text-gray-600">({{ $u->role_requested_at }})</span>
                    @endif
                </div>

                <div class="mt-3 flex gap-3">
                    <form method="POST" action="{{ route('admin.users.approve', $u) }}">
                        @csrf
                        @method('PUT')
                        <button class="underline">Aprobar</button>
                    </form>

                    <form method="POST" action="{{ route('admin.users.reject', $u) }}">
                        @csrf
                        @method('PUT')
                        <button class="underline">Rechazar</button>
                    </form>
                </div>
            </div>
        @empty
            <p>No hay solicitudes pendientes.</p>
        @endforelse
    </div>
@endsection
