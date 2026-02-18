<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adopciones</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#0d9488',
                        brandSoft: '#F5E7DA',
                        brandNavy: '#243B6B',
                    },
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900">
    <!-- Layout wrapper: full height + column -->
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-teal-700 text-black">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <a href="/" class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-white/10">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 10.5c1.38 0 2.5-1.12 2.5-2.5S13.38 5.5 12 5.5 9.5 6.62 9.5 8s1.12 2.5 2.5 2.5z"/>
                                    <path d="M12 12c-3.31 0-6 2.69-6 6h2a4 4 0 018 0h2c0-3.31-2.69-6-6-6z"/>
                                </svg>
                            </span>
                            <span class="tracking-wide leading-none">
                                DEJANDO<br>HUELLA
                            </span>
                        </a>
                    </div>
                    <div class="flex items-center gap-8 text-base absolute left-1/2 transform -translate-x-1/2">
                        <a href="{{ url('/') }}" class="hover:text-white/80 transition">Inicio</a>
                        <a href="{{ route('adopciones.form') }}" class="hover:text-white/80 transition">Adopción</a>
                        <a href="#" class="hover:text-white/80 transition">Servicios Medicos</a>
                    </div>
                    <div class="flex items-center">
                        <a href="{{ route('login') }}" class="bg-[#F5E7DA] text-black px-8 py-2 rounded-full hover:opacity-90 transition">
                            Iniciar sesión
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900">Adopción de Mascotas</h1>
                <p class="text-gray-600 mt-2">Registra una mascota disponible para adopción</p>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <!-- Alert -->
                <div id="alert" class="mb-4 hidden rounded-lg p-4 text-sm font-medium">
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Form Card -->
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
        </main>

        <!-- Footer -->
        <footer class="bg-teal-700 text-black">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10 grid grid-cols-1 md:grid-cols-3 gap-10">

                <div>
                    <h3 class="text-2xl font-bold mb-4">Información Animal</h3>
                    <ul class="space-y-3 text-white/90 font-semibold">
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-1.5 w-1.5 rounded-full bg-white"></span>
                            Alimentación Animal
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-1.5 w-1.5 rounded-full bg-white"></span>
                            Enfermedades comunes
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-1.5 w-1.5 rounded-full bg-white"></span>
                            Esterilización/Vacunación
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-2xl font-bold mb-4">Información de contacto</h3>
                    <ul class="space-y-3 text-white/90 font-semibold">
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-1.5 w-1.5 rounded-full bg-white"></span>
                            Blvd de la Juventud 1006A, Solares 20 de Noviembre, 34288 Durango, Dgo.
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-1.5 w-1.5 rounded-full bg-white"></span>
                            +52 618 - 137 - 8344
                        </li>
                    </ul>
                </div>

                <div class="md:text-right">
                    <h3 class="text-2xl font-bold mb-4">Redes Sociales</h3>
                    <div class="flex md:justify-end gap-6">
                        <a href="#" class="h-14 w-14 rounded-full bg-[#243B6B] flex items-center justify-center hover:opacity-90 transition" aria-label="Facebook">
                            <span class="text-2xl font-black">f</span>
                        </a>
                        <a href="#" class="h-14 w-14 rounded-full bg-[#243B6B] flex items-center justify-center hover:opacity-90 transition" aria-label="Email">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </footer>
    </div>

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
</body>
</html>
