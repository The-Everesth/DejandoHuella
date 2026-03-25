<x-app-layout>
    <section class="space-y-24">
        {{-- HERO + ACCESOS --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 mb-20 mt-24">
            <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm sm:p-10 lg:col-span-6">
                <p class="text-sm font-semibold text-teal-700">Dejando Huella</p>
                <h1 class="mt-3 text-4xl font-black leading-tight text-gray-900 sm:text-5xl">
                    Adopta, cuida y conecta con mascotas que necesitan un hogar
                </h1>
                <p class="mt-6 text-xl leading-relaxed text-gray-600">
                    Encuentra mascotas en adopción, agenda citas veterinarias y gestiona refugios en un solo lugar.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    @guest
                        <a href="{{ route('login') }}" class="inline-block">
                            <x-button variant="primary" class="px-8 py-3 text-base">
                                Iniciar sesión
                            </x-button>
                        </a>
                        <a href="{{ route('register') }}" class="inline-block">
                            <x-button variant="outline" class="px-8 py-3 text-base">
                                Crear cuenta
                            </x-button>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="inline-block">
                            <x-button variant="primary" class="px-8 py-3 text-base">
                                Ir a mi panel
                            </x-button>
                        </a>
                        <a href="{{ route('adopciones.form') }}" class="inline-block">
                            <x-button class="bg-gray-200 px-8 py-3 text-base text-gray-900 hover:bg-gray-300">
                                Explorar adopciones
                            </x-button>
                        </a>
                    @endguest
                </div>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8 lg:col-span-6">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:auto-rows-fr">
                    <a href="{{ auth()->check() ? route('adopciones.form') : route('login') }}" class="group flex h-full w-full flex-col justify-between rounded-2xl border border-teal-100 bg-teal-50 p-5 transition hover:-translate-y-0.5 hover:border-teal-200 lg:min-h-[180px]">
                                                <span class="mb-1 inline-block">
                                                        <!-- Huella clásica tipo imagen adjunta -->
                                                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" stroke="#0d9488" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8">
                                                            <ellipse cx="16" cy="22" rx="8" ry="6"/>
                                                            <ellipse cx="8.5" cy="13" rx="3" ry="4"/>
                                                            <ellipse cx="23.5" cy="13" rx="3" ry="4"/>
                                                            <ellipse cx="12" cy="8.5" rx="2.2" ry="3"/>
                                                            <ellipse cx="20" cy="8.5" rx="2.2" ry="3"/>
                                                        </svg>
                                                </span>
                        <h3 class="mt-2 text-2xl font-bold text-gray-900">Adopciones</h3>
                        <p class="mt-1 text-base text-gray-600">Publica, solicita y administra.</p>
                    </a>

                    <a href="{{ auth()->check() ? route('services.index') : route('login') }}" class="group flex h-full w-full flex-col justify-between rounded-2xl border border-teal-100 bg-teal-50 p-5 transition hover:-translate-y-0.5 hover:border-teal-200 lg:min-h-[180px]">
                        <span class="mb-1 inline-block">
                            <!-- Cruz médica solo contorno -->
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8">
                                <rect x="9" y="2" width="6" height="20" rx="2"/>
                                <rect x="2" y="9" width="20" height="6" rx="2"/>
                            </svg>
                        </span>
                        <h3 class="mt-2 text-2xl font-bold text-gray-900">Servicios Médicos</h3>
                        <p class="mt-1 text-base text-gray-600">Clínicas, citas y atención.</p>
                    </a>

                    <a href="{{ auth()->check() ? route('adopciones.form') : route('register') }}" class="group flex h-full w-full flex-col justify-between rounded-2xl border border-teal-100 bg-teal-50 p-5 transition hover:-translate-y-0.5 hover:border-teal-200 lg:min-h-[180px]">
                        <span class="mb-1 inline-block">
                            <!-- Casa/refugio solo contorno -->
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8">
                                <path d="M3 11.5L12 4l9 7.5"/>
                                <path d="M4 10v10a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-4h4v4a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1V10"/>
                            </svg>
                        </span>
                        <h3 class="mt-2 text-2xl font-bold text-gray-900">Refugios</h3>
                        <p class="mt-1 text-base text-gray-600">Gestión de mascotas y adopción.</p>
                    </a>

                    <a href="{{ auth()->check() ? route('tickets.index') : route('login') }}" class="group flex h-full w-full flex-col justify-between rounded-2xl border border-teal-100 bg-teal-50 p-5 transition hover:-translate-y-0.5 hover:border-teal-200 lg:min-h-[180px]">
                        <span class="mb-1 inline-block">
                            <!-- Mensaje/chat solo contorno -->
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                        </span>
                        <h3 class="mt-2 text-2xl font-bold text-gray-900">Soporte</h3>
                        <p class="mt-1 text-base text-gray-600">Mensajes a administración.</p>
                    </a>
                </div>
            </div>
        </div>

        <section class="py-12"> <div class="mt-8 mb-20 grid grid-cols-1 gap-8 md:grid-cols-3">
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 text-center">
            <div class="text-5xl font-black text-blue-700" id="counter-users">0</div>
            <div class="mt-3 text-2xl font-semibold text-blue-900">Usuarios registrados</div>
        </div>
        
            <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-center">
            <div class="text-5xl font-black text-green-700" id="counter-adoptions">0</div>
            <div class="mt-3 text-2xl font-semibold text-green-900">Mascotas en adopción</div>
        </div>
        
            <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4 text-center">
            <div class="text-5xl font-black text-indigo-700" id="counter-clinics">0</div>
            <div class="mt-3 text-2xl font-semibold text-indigo-900">Clínicas registradas</div>
        </div>
    </div>
</section>

<section class="mt-32 mb-40 max-w-7xl mx-auto px-4 mb-40"> 
    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
        
        <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
            <span class="mb-3 inline-block">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" stroke="#0d9488" stroke-width="2.2" class="w-8 h-8">
                    <ellipse cx="16" cy="22" rx="8" ry="6"/><ellipse cx="8.5" cy="13" rx="3" ry="4"/><ellipse cx="23.5" cy="13" rx="3" ry="4"/><ellipse cx="12" cy="8.5" rx="2.2" ry="3"/><ellipse cx="20" cy="8.5" rx="2.2" ry="3"/>
                </svg>
            </span>
            <h3 class="mt-3 text-2xl font-bold text-gray-900">Adopta mascotas</h3>
            <p class="mt-2 text-lg leading-relaxed text-gray-600">Encuentra y solicita la adopción de mascotas que necesitan un hogar seguro.</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
            <span class="mb-1 inline-block">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="1.5" class="w-8 h-8">
                    <rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            </span>
            <h3 class="mt-3 text-2xl font-bold text-gray-900">Agenda citas</h3>
            <p class="mt-2 text-lg leading-relaxed text-gray-600">Consulta clínicas y servicios veterinarios para el bienestar de tus mascotas.</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
            <span class="mb-1 inline-block">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="1.5" class="w-8 h-8">
                    <path d="M3 11.5L12 4l9 7.5"/><path d="M4 10v10a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-4h4v4a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1V10"/>
                </svg>
            </span>
            <h3 class="mt-3 text-2xl font-bold text-gray-900">Gestiona refugios</h3>
            <p class="mt-2 text-lg leading-relaxed text-gray-600">Administra publicaciones y solicitudes de adopción de forma centralizada.</p>
        </div>
    </div>
</section>

<footer class="bg-[#0d9488] text-white pt-20 pb-10 mt-24">
    <div class="max-w-7xl mx-auto px-4">
        </div>
</footer>
    <script>
        async function loadCounters() {
            try {
                const response = await fetch('/api/system-stats');
                if (!response.ok) throw new Error('Failed to fetch stats');

                const data = await response.json();
                document.getElementById('counter-users').textContent = data.users_count || 0;
                document.getElementById('counter-adoptions').textContent = data.adoptions_count || 0;
                document.getElementById('counter-clinics').textContent = data.clinics_count || 0;
            } catch (error) {
                console.error('Error loading counters:', error);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', loadCounters);
        } else {
            loadCounters();
        }
    </script>
</x-app-layout>
