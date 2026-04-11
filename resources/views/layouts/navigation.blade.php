<nav x-data="{ open: false }" class="bg-teal-700 text-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-24 flex items-center justify-between">

            <!-- Logo -->
            <div class="flex items-center gap-4">
                <a href="{{ url('/') }}" class="flex items-center gap-3 hover:opacity-90 transition">
                    {{-- Logo real del proyecto --}}
                    <span class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-white/80 hover:bg-white/90 transition overflow-hidden border-2 border-white shadow-sm">
                        <img src="/img/logo-dejandohuella.png" alt="Logo Dejando Huella" style="width:64px;height:64px;" />
                    </span>
                    </span>
                    <span class="font-extrabold tracking-wider leading-none text-lg">
                        DEJANDO<br>HUELLA
                    </span>
                </a>
            </div>

            <!-- Desktop Links -->
            <div class="hidden md:flex items-center gap-8 font-semibold text-xl">
                <a href="{{ url('/') }}" class="hover:text-white/80 transition">Inicio</a>

                {{-- Ajusta estas rutas a las tuyas reales --}}
                <a href="{{ route('adopciones.form') }}" class="hover:text-white/80 transition">Adopciones</a>


                <a href="{{ route('services.index') }}" class="hover:text-white/80 transition">Servicios Medicos</a>

            </div>

            <!-- Right side (Login pill / User pill) -->
            <div class="hidden md:flex items-center mr-1 lg:mr-3">
                @guest
                    <a href="{{ route('login') }}"
                       class="bg-[#F5E7DA] text-black font-bold px-8 py-2 rounded-full hover:opacity-90 transition">
                        Iniciar sesión
                    </a>
                @else
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 hover:opacity-95 transition">
                                <span class="inline-flex items-center gap-2 rounded-full bg-[#F5E7DA] px-5 py-2 text-black font-bold max-w-xs">
                                    <span class="truncate max-w-[11rem]">{{ Auth::user()->name }}</span>
                                    <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.25 8.29a.75.75 0 01-.02-1.08z" clip-rule="evenodd" />
                                    </svg>
                                </span>

                                @if (Auth::user()->profile_photo_url)
                                    <img
                                        src="{{ Auth::user()->profile_photo_url }}"
                                        alt="Foto de perfil"
                                        class="h-10 w-10 rounded-full object-cover ring-2 ring-white/60 shadow-sm"
                                    >
                                @else
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-xs font-extrabold text-white ring-2 ring-white/30">
                                        {{ Auth::user()->profile_initials }}
                                    </span>
                                @endif
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Perfil</x-dropdown-link>
                            <x-dropdown-link :href="route('dashboard')">Dashboard</x-dropdown-link>

                            <div class="border-t my-1"></div>

                                {{-- DEBUG: Mostrar roles actuales del usuario autenticado --}}
                                @if(auth()->check())
                                    <div class="px-4 py-2 text-xs text-gray-600 bg-yellow-100 rounded mb-2">
                                        <strong>Tipo de usuario: </strong>
                                        @php($roles = auth()->user()->getRoleNames())
                                        @if($roles && count($roles))
                                            {{ implode(', ', $roles->toArray()) }}
                                        @else
                                            <span class="text-red-600">Sin roles</span>
                                        @endif
                                    </div>
                                @endif

                            {{-- Links por rol (en dropdown para mantener el estilo del figma limpio) --}}

                            @role('admin')
                                <x-dropdown-link :href="route('admin.dashboard')">Admin Panel</x-dropdown-link>
                                <x-dropdown-link :href="route('admin.users.index')">Usuarios</x-dropdown-link>
                                <x-dropdown-link :href="route('admin.tickets.index')">Tickets</x-dropdown-link>
                                <x-dropdown-link :href="route('admin.adoptions.index')">Gestión de adopciones</x-dropdown-link>
                            @endrole

                            @role('veterinario')
                                <x-dropdown-link :href="route('vet.my.adoptions')">Mis adopciones</x-dropdown-link>
                                <x-dropdown-link :href="route('vet.clinics.index')">Mis Clínicas</x-dropdown-link>
                                <x-dropdown-link :href="route('vet.services.index')">Mis servicios</x-dropdown-link>
                                <x-dropdown-link :href="route('vet.appointments.index')">Citas</x-dropdown-link>
                                <x-dropdown-link :href="route('my.published.requests')">Solicitudes de adopción</x-dropdown-link>
                                <!--<x-dropdown-link :href="route('vet.clinics.index')">Gestionar servicios médicos</x-dropdown-link>-->
                            @endrole

                            @role('refugio')
                                <x-dropdown-link :href="route('vet.my.adoptions')">Mis adopciones</x-dropdown-link>
                                <x-dropdown-link :href="route('my.published.requests')">Solicitudes de adopción</x-dropdown-link>
                            @endrole
                            
                            @role('ciudadano')
                                <x-dropdown-link :href="route('my.pets')">
                                    Mis mascotas
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('my.appointments')">
                                    Mis citas
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('my.requests')">
                                    Mis solicitudes
                                </x-dropdown-link>
                                
                            @endrole

                            <x-dropdown-link :href="route('tickets.index')">Mensajes</x-dropdown-link>

                            <div class="border-t my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Cerrar sesión
                                </button>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endguest
            </div>

            <!-- Hamburger (mobile) -->
            <div class="md:hidden">
                <button @click="open = ! open" class="p-2 rounded-md hover:bg-white/10 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden border-t border-white/10">
        <div class="px-4 py-3 space-y-2 font-semibold">
            <a class="block py-2" href="{{ url('/') }}">Inicio</a>
            <a class="block py-2" href="{{ route('adopciones.form') }}">Adopción</a>
            <a class="block py-2" href="{{ route('services.index') }}">Servicios Medicos</a>

            <div class="border-t border-white/10 my-2"></div>

            @guest
                <a class="block bg-[#F5E7DA] text-black font-bold px-4 py-2 rounded-full text-center"
                   href="{{ route('login') }}">Login</a>
            @else
                <a class="block py-2" href="{{ route('profile.edit') }}">Perfil</a>
                <a class="block py-2" href="{{ route('dashboard') }}">Dashboard</a>
                <a class="block py-2" href="{{ route('tickets.index') }}">Mensajes</a>

                @role('admin')
                    <div class="border-t border-white/10 my-2"></div>
                    <a class="block py-2" href="{{ route('admin.dashboard') }}">Admin Panel</a>
                    <a class="block py-2" href="{{ route('admin.users.index') }}">Usuarios</a>
                    {{-- <a class="block py-2" href="{{ route('admin.services.index') }}">Servicios</a> --}}
                    <a class="block py-2" href="{{ route('admin.tickets.index') }}">Tickets</a>
                    <a class="block py-2" href="{{ route('admin.adoptions.index') }}">Gestión de adopciones</a>
                @endrole

                @role('veterinario')
                    <div class="border-t border-white/10 my-2"></div>
                    <a class="block py-2" href="{{ route('vet.my.adoptions') }}">Mis adopciones</a>
                    <a class="block py-2" href="{{ route('vet.clinics.index') }}">Mis Clínicas</a>
                    <a class="block py-2" href="{{ route('vet.appointments.index') }}">Citas</a>
                    <a class="block py-2" href="{{ route('my.published.requests') }}">Solicitudes de adopción</a>
                @endrole

                @role('refugio')
                    <div class="border-t border-white/10 my-2"></div>
                    <a class="block py-2" href="{{ route('vet.my.adoptions') }}">Mis adopciones</a>
                    <a class="block py-2" href="{{ route('my.published.requests') }}">Solicitudes de adopción</a>
                @endrole

                <form method="POST" action="{{ route('logout') }}" class="pt-2">
                    @csrf
                    <button class="w-full bg-[#F5E7DA] text-black font-bold px-4 py-2 rounded-full">
                        Cerrar sesión
                    </button>
                </form>
            @endguest
        </div>
    </div>
</nav>
