<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Heading -->
            <header class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Adopción de Mascotas</h1>
                <p class="text-gray-600 mt-2">Registra una mascota disponible para adopción</p>
            </header>

            <!-- Alert -->
            <div id="alert" class="mb-4 hidden rounded-lg p-4 text-sm font-medium"></div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Form Card -->
                    @auth
                        @role('refugio')
                            <div class="bg-white rounded-lg shadow">
                                <div class="p-6">
                                    <h2 class="text-xl font-bold text-gray-900 mb-6">Registro de mascota</h2>

                                    <form id="adopcionForm" class="space-y-4">
                                <div>
                                    <label for="nombreAnimal" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nombre del animal <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="nombreAnimal" 
                                        name="nombreAnimal" 
                                        placeholder="Ej: Max, Luna..." 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-transparent"
                                    >
                                </div>

                                <div>
                                    <label for="tipoAnimal" class="block text-sm font-medium text-gray-700 mb-1">
                                        Tipo de animal <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        id="tipoAnimal" 
                                        name="tipoAnimal"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-transparent"
                                    >
                                        <option value="">Selecciona un tipo...</option>
                                        <option value="Perro">Perro</option>
                                        <option value="Gato">Gato</option>
                                        <option value="Conejo">Conejo</option>
                                        <option value="Ave">Ave</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="edad" class="block text-sm font-medium text-gray-700 mb-1">
                                        Edad (años) <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        id="edad" 
                                        name="edad" 
                                        min="0" 
                                        max="50" 
                                        placeholder="3" 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-transparent"
                                    >
                                </div>

                                <div>
                                    <label for="raza" class="block text-sm font-medium text-gray-700 mb-1">
                                        Raza <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="raza" 
                                        name="raza" 
                                        placeholder="Ej: Golden Retriever..." 
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-transparent"
                                    >
                                </div>

                                <div>
                                    <label for="detalles" class="block text-sm font-medium text-gray-700 mb-1">
                                        Detalles adicionales
                                    </label>
                                    <textarea 
                                        id="detalles" 
                                        name="detalles" 
                                        placeholder="Descripción del carácter, personalidad, necesidades especiales..."
                                        rows="4"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-transparent"
                                    ></textarea>
                                </div>

                                <button 
                                    type="submit" 
                                    class="w-full bg-[#F5E7DA] text-black font-bold py-3 px-4 rounded-full hover:opacity-90 transition"
                                >
                                    Registrar adopción
                                </button>
                            </form>
                        </div>
                    </div>
                        @else
                            <div class="bg-red-50 rounded-lg shadow border border-red-200">
                                <div class="p-6">
                                    <h2 class="text-xl font-bold text-red-900 mb-4">Acceso denegado</h2>
                                    <p class="text-red-800 mb-4">Solo los refugios registrados pueden publicar mascotas para adopción.</p>
                                    <p class="text-gray-700">Si eres un refugio, contacta al administrador para solicitar este acceso.</p>
                                </div>
                            </div>
                        @endrole
                    @else
                        <div class="bg-blue-50 rounded-lg shadow border border-blue-200">
                            <div class="p-6">
                                <h2 class="text-xl font-bold text-blue-900 mb-4">Inicia sesión para registrar mascotas</h2>
                                <p class="text-blue-800 mb-6">Si eres parte de un refugio, inicia sesión para publicar mascotas disponibles para adopción.</p>
                                <a href="{{ route('login') }}" class="inline-block bg-[#F5E7DA] text-black font-bold py-3 px-6 rounded-full hover:opacity-90 transition">
                                    Iniciar sesión
                                </a>
                            </div>
                        </div>
                    @endauth

                    <!-- List Card -->
                    <div class="bg-white rounded-lg shadow-lg border border-slate-100">
                        <div class="p-6">
                            <div class="mb-6 pb-4 border-b border-slate-200">
                                <h2 class="text-2xl font-bold text-gray-900">Adopciones registradas</h2>
                            </div>
                            <div id="adopcionList" class="space-y-4 max-h-96 overflow-y-auto pr-2">
                                <div class="text-center py-12">
                                    <div class="animate-pulse">
                                        <p class="text-gray-500 text-lg">Cargando adopciones...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
        const API_URL = '/api/adoptions';

        // Función para mostrar alertas con Tailwind
        function showAlert(message, type) {
            const alertDiv = document.getElementById('alert');
            alertDiv.textContent = message;
            
            if (type === 'success') {
                alertDiv.className = 'mb-4 rounded-lg p-4 text-sm font-medium bg-green-50 text-green-800 border border-green-200';
            } else {
                alertDiv.className = 'mb-4 rounded-lg p-4 text-sm font-medium bg-red-50 text-red-800 border border-red-200';
            }
            
            alertDiv.classList.remove('hidden');

            if (type === 'success') {
                setTimeout(() => {
                    alertDiv.classList.add('hidden');
                }, 4000);
            }
        }

        // Función para cargar adopciones desde la API
        async function loadAdopciones() {
            const adopcionList = document.getElementById('adopcionList');
            
            try {
                const response = await fetch(API_URL);
                const result = await response.json();

                if (result.success && result.data) {
                    adopcionList.innerHTML = '';
                    
                    // Convertir objeto a array y ordenar por fecha
                    const adopcionesArray = Array.isArray(result.data) 
                        ? result.data
                        : Object.entries(result.data).map(([id, data]) => ({ id, ...data }));

                    adopcionesArray.sort((a, b) => new Date(b.fecha) - new Date(a.fecha));

                    if (adopcionesArray.length === 0) {
                        adopcionList.innerHTML = '<div class="text-center py-12"><p class="text-gray-500 text-lg">No hay adopciones registradas aún</p><p class="text-gray-400 text-sm mt-2">¡Sé el primero en registrar una mascota!</p></div>';
                        return;
                    }

                    adopcionesArray.forEach((adopcion) => {
                        const fecha = new Date(adopcion.fecha).toLocaleDateString('es-ES');
                        const html = `
                            <div class="group p-4 rounded-2xl border bg-teal-50 hover:shadow-md transition-all duration-200 cursor-pointer">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h3 class="text-lg font-extrabold text-gray-900">${adopcion.nombreAnimal}</h3>
                                        <p class="text-sm text-gray-600">${adopcion.tipoAnimal}</p>
                                    </div>
                                    <span class="inline-block bg-teal-700 text-white text-xs font-semibold px-3.5 py-1.5 rounded-full shadow-sm">${adopcion.estado}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <span class="text-sm text-gray-700"><strong>Edad:</strong> ${adopcion.edad} año${adopcion.edad != 1 ? 's' : ''}</span>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-700"><strong>Raza:</strong> ${adopcion.raza}</span>
                                    </div>
                                </div>
                                ${adopcion.detalles ? `<div class="bg-white rounded-lg p-3 mb-3 border">
                                    <p class="text-sm text-gray-600"><strong class="text-gray-900">Detalles:</strong> ${adopcion.detalles}</p>
                                </div>` : ''}
                                <div class="flex items-center justify-between pt-3 border-t border-teal-200">
                                    <span class="text-xs text-gray-600">Registrado el ${fecha}</span>
                                </div>
                            </div>
                        `;
                        adopcionList.innerHTML += html;
                    });
                } else {
                    adopcionList.innerHTML = '<div class="text-center py-12"><p class="text-gray-500 text-lg">No hay adopciones registradas aún</p><p class="text-gray-400 text-sm mt-2">¡Sé el primero en registrar una mascota!</p></div>';
                }
            } catch (error) {
                console.error('Error al cargar adopciones:', error);
                adopcionList.innerHTML = '<div class="text-center py-12"><p class="text-red-600 text-lg font-medium">Error al cargar adopciones</p><p class="text-gray-400 text-sm mt-2">Intenta recargar la página</p></div>';
            }
        }

        // Cargar adopciones al iniciar
        loadAdopciones();

        // Recargar adopciones cada 3 segundos
        setInterval(loadAdopciones, 3000);

        // Agregar evento para el formulario
        document.getElementById('adopcionForm').addEventListener('submit', async function (event) {
            event.preventDefault();

            const nombreAnimal = document.getElementById('nombreAnimal').value.trim();
            const tipoAnimal = document.getElementById('tipoAnimal').value;
            const edad = document.getElementById('edad').value;
            const raza = document.getElementById('raza').value.trim();
            const detalles = document.getElementById('detalles').value.trim();

            // Validaciones básicas
            if (!nombreAnimal || !raza || !edad || !tipoAnimal) {
                showAlert('Por favor, completa todos los campos obligatorios', 'error');
                return;
            }

            if (edad < 0 || edad > 50) {
                showAlert('Por favor, ingresa una edad válida (0-50)', 'error');
                return;
            }

            // Desactivar botón mientras se envía
            const btn = document.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Registrando...';

            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        nombreAnimal,
                        tipoAnimal,
                        edad: parseInt(edad),
                        raza,
                        detalles: detalles || '',
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    showAlert('¡Adopción registrada con éxito!', 'success');
                    document.getElementById('adopcionForm').reset();
                    loadAdopciones();
                } else {
                    showAlert('Error: ' + (result.message || 'Error al guardar'), 'error');
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'error');
                console.error('Error:', error);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Registrar Adopción';
            }
        });
    </script>
