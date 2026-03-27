@extends('layouts.app')

@section('content')
    <x-page-title title="Usuarios" subtitle="Administración de cuentas y roles." />

    <x-card class="mb-4">
        <div class="flex flex-col gap-4">

            {{-- Tabs --}}
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['trashed' => null])) }}">
                    <x-button variant="{{ empty($trashed) ? 'soft' : 'outline' }}">Activos</x-button>
                </a>

                <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['trashed' => 'only'])) }}">
                    <x-button variant="{{ ($trashed === 'only') ? 'soft' : 'outline' }}">Inactivos</x-button>
                </a>

                <a href="{{ route('admin.users.index', array_merge(request()->except('page'), ['trashed' => 'with'])) }}">
                    <x-button variant="{{ ($trashed === 'with') ? 'soft' : 'outline' }}">Todos</x-button>
                </a>
            </div>

            {{-- Filtros --}}
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                <input type="hidden" name="trashed" value="{{ $trashed }}">

                <div class="md:col-span-2">
                    <label class="font-bold text-gray-800">Buscar</label>
                    <input name="q" value="{{ $q }}" class="mt-2 w-full border rounded-xl p-3" placeholder="Nombre o correo...">
                </div>

                <div>
                    <label class="font-bold text-gray-800">Rol</label>
                    <select name="role" class="mt-2 w-full border rounded-xl p-3">
                        <option value="">Todos</option>
                        <option value="admin" @selected($role==='admin')>Administrador</option>
                        <option value="veterinario" @selected($role==='veterinario')>Veterinario</option>
                        <option value="refugio" @selected($role==='refugio')>Refugio</option>
                        <option value="ciudadano" @selected($role==='ciudadano')>Ciudadano</option>
                    </select>
                </div>

                <div>
                    <label class="font-bold text-gray-800">Solicitud</label>
                    <select name="status" class="mt-2 w-full border rounded-xl p-3">
                        <option value="all" @selected(($status ?? 'all')==='all')>Todas</option>
                        <option value="pending" @selected(($status ?? '')==='pending')>Pendiente</option>
                        <option value="approved" @selected(($status ?? '')==='approved')>Aprobada</option>
                        <option value="rejected" @selected(($status ?? '')==='rejected')>Rechazada</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <x-button type="submit" variant="soft" class="w-full">Filtrar</x-button>

                    <a href="{{ route('admin.users.index', ['trashed' => $trashed]) }}" class="w-full">
                        <x-button variant="outline" class="w-full">Limpiar</x-button>
                    </a>
                </div>
            </form>
        </div>
    </x-card>

    <x-card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-slate-600">
                        <th class="p-4 font-bold">Usuario</th>
                        <th class="p-4 font-bold">Roles</th>
                        <th class="p-4 font-bold">Estado</th>
                        <th class="p-4 font-bold">Creado</th>
                        <th class="p-4 font-bold text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50 {{ ($u->status ?? null) === 'inactive' ? 'opacity-70' : '' }}">
                            <td class="p-4">
                                <div class="font-extrabold text-slate-900">{{ $u->name }}</div>
                                <div class="text-slate-600">{{ $u->email }}</div>
                            </td>

                            <td class="p-4">
                                @php($rolesList = method_exists($u, 'getRoleNames') ? $u->getRoleNames() : collect())
                                @if($rolesList->count())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($rolesList as $r)
                                            <span class="px-3 py-1 rounded-full bg-teal-100 text-teal-900 font-bold">{{ $r }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-800 font-bold">sin rol</span>
                                @endif
                            </td>

                            <td class="p-4">
                                <div class="flex flex-col gap-2">
                                    {{-- Estado del usuario --}}
                                    <span class="px-3 py-1 rounded-full font-bold w-fit
                                        {{ ($u->status ?? null) === 'inactive' ? 'bg-red-100 text-red-900' : 'bg-green-100 text-green-900' }}">
                                        {{ ($u->status ?? null) === 'inactive' ? 'Inactivo' : 'Activo' }}
                                    </span>

                                    {{-- Estado de solicitud de rol --}}
                                    @if($u->role_request_status)
                                        @switch($u->role_request_status)
                                            @case('pending')
                                                @php($tone = 'bg-amber-100 text-amber-900')
                                                @break
                                            @case('approved')
                                                @php($tone = 'bg-green-100 text-green-900')
                                                @break
                                            @case('rejected')
                                                @php($tone = 'bg-red-100 text-red-900')
                                                @break
                                            @default
                                                @php($tone = 'bg-gray-100 text-gray-800')
                                        @endswitch

                                        <span class="px-3 py-1 rounded-full font-bold w-fit {{ $tone }}">
                                            Solicitud: {{ $u->role_request_status }}
                                        </span>
                                    @endif

                                </div>
                            </td>

                            <td class="p-4 text-slate-600">
                                {{ $u->created_at ? (method_exists($u->created_at, 'format') ? $u->created_at->format('Y-m-d') : (is_string($u->created_at) ? \Carbon\Carbon::parse($u->created_at)->format('Y-m-d') : $u->created_at)) : '-' }}
                            </td>

                            <td class="p-4">
                                <div class="flex justify-end gap-2 flex-wrap">
                                    @php($isMe = auth()->id() === $u->id)

                                    <a href="{{ route('admin.users.role.edit', $u->id) }}">
                                        <x-button variant="outline">Rol</x-button>
                                    </a>

                                    <a href="{{ route('admin.users.edit', $u->id) }}">
                                        <x-button variant="outline">Editar</x-button>
                                    </a>

                                    @if(($u->status ?? null) === 'inactive')
                                        <form method="POST" action="{{ route('admin.users.restore', $u->id) }}"
                                              onsubmit="return confirm('¿Restaurar este usuario?');">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full font-bold
                                                    bg-gray-900 text-white hover:opacity-90 transition">
                                                Restaurar
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}"
                                              onsubmit="return confirm('¿Desactivar este usuario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                {{ $isMe ? 'disabled' : '' }}
                                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full font-bold bg-red-600 text-white hover:bg-red-700 transition {{ $isMe ? 'opacity-50 cursor-not-allowed' : '' }}">
                                                Desactivar
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                @if($isMe)
                                    <div class="text-xs text-slate-500 mt-2 text-right">Este eres tú</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="p-6 text-center text-slate-600" colspan="5">
                                No hay usuarios con esos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $users->links() }}
        </div>
    </x-card>
@endsection
