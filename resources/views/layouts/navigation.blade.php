<nav x-data="{ open: false }" class="bg-teal-700 text-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center justify-between">

            <!-- Logo -->
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    {{-- Si tienes logo real, reemplaza este svg por <img> --}}
                    <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 10.5c1.38 0 2.5-1.12 2.5-2.5S13.38 5.5 12 5.5 9.5 6.62 9.5 8s1.12 2.5 2.5 2.5z"/>
                            <path d="M12 12c-3.31 0-6 2.69-6 6h2a4 4 0 018 0h2c0-3.31-2.69-6-6-6z"/>
                        </svg>
                    </span>
                    <span class="font-extrabold tracking-wide leading-none">
                        DEJANDO<br>HUELLA
                    </span>
                </a>
            </div>

            <!-- Desktop Links -->
            <div class="hidden md:flex items-center gap-8 font-semibold text-lg">
                <a href="{{ url('/') }}" class="hover:text-white/80 transition">Inicio</a>

                {{-- Ajusta estas rutas a las tuyas reales --}}
                <a href="{{ route('adopciones.form') }}" class="hover:text-white/80 transition">Adopción</a>


                <a href="{{ route('services.index') }}" class="hover:text-white/80 transition">Servicios Medicos</a>

            </div>

            <!-- Right side (Login pill / User pill) -->
            <div class="hidden md:flex items-center">
                @guest
                    <a href="{{ route('login') }}"
                       class="bg-[#F5E7DA] text-black font-bold px-8 py-2 rounded-full hover:opacity-90 transition">
                        Iniciar sesión
                    </a>
                @else
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="bg-[#F5E7DA] text-black font-bold px-6 py-2 rounded-full hover:opacity-90 transition inline-flex items-center gap-2">
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.25 8.29a.75.75 0 01-.02-1.08z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('dashboard')">Dashboard</x-dropdown-link>
                            <x-dropdown-link :href="route('profile.edit')">Perfil</x-dropdown-link>

                            <div class="border-t my-1"></div>

                            {{-- Links por rol (en dropdown para mantener el estilo del figma limpio) --}}
                            @role('admin')
                                <x-dropdown-link :href="route('admin.dashboard')">Admin Panel</x-dropdown-link>
                                <x-dropdown-link :href="route('admin.users.index')">Usuarios</x-dropdown-link>
                                <x-dropdown-link :href="route('admin.services.index')">Servicios</x-dropdown-link>
                                <x-dropdown-link :href="route('admin.tickets.index')">Tickets</x-dropdown-link>
                            @endrole

                            @role('veterinario')
                                <x-dropdown-link :href="route('vet.clinics.index')">Mis Clínicas</x-dropdown-link>
                                <x-dropdown-link :href="route('vet.appointments.index')">Citas</x-dropdown-link>
                            @endrole

                            <x-dropdown-link :href="route('tickets.index')">Mensajes</x-dropdown-link>

                            <div class="border-t my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    Cerrar sesión
                                </x-dropdown-link>
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
                <a class="block py-2" href="{{ route('dashboard') }}">Dashboard</a>
                <a class="block py-2" href="{{ route('profile.edit') }}">Perfil</a>
                <a class="block py-2" href="{{ route('tickets.index') }}">Mensajes</a>

                @role('admin')
                    <div class="border-t border-white/10 my-2"></div>
                    <a class="block py-2" href="{{ route('admin.dashboard') }}">Admin Panel</a>
                    <a class="block py-2" href="{{ route('admin.users.index') }}">Usuarios</a>
                    <a class="block py-2" href="{{ route('admin.services.index') }}">Servicios</a>
                    <a class="block py-2" href="{{ route('admin.tickets.index') }}">Tickets</a>
                @endrole

                @role('veterinario')
                    <div class="border-t border-white/10 my-2"></div>
                    <a class="block py-2" href="{{ route('vet.clinics.index') }}">Mis Clínicas</a>
                    <a class="block py-2" href="{{ route('vet.appointments.index') }}">Citas</a>
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
