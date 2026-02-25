<x-app-layout>
    @php($canRegisterAdoption = auth()->check() && auth()->user()->hasAnyRole(['admin', 'veterinario', 'refugio']))
    @php($canManageAdoptionImage = auth()->check() && auth()->user()->hasAnyRole(['admin', 'veterinario', 'refugio']))
    @php($canDeleteAdoption = auth()->check() && auth()->user()->hasAnyRole(['admin', 'veterinario', 'refugio']))

    <x-slot name="header">
        <h1 class="text-3xl font-bold text-gray-900">Adopción de Mascotas</h1>
        <p class="text-gray-600 mt-2">Registra una mascota disponible para adopción</p>
    </x-slot>

                <!-- Alert -->
                <div id="alert" class="mb-4 hidden rounded-lg p-4 text-sm font-medium">
                </div>

                <div class="grid grid-cols-1 {{ $canRegisterAdoption ? 'lg:grid-cols-2' : '' }} gap-8">
                    @if($canRegisterAdoption)
                    <!-- Form Card -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-6">Registro de mascota</h2>
                            <div id="formAccessNotice" class="mb-4 hidden rounded-lg p-3 text-sm font-medium"></div>

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

                                <div>
                                    <label for="fotoMascota" class="block text-sm font-medium text-gray-700 mb-1">
                                        Foto de la mascota
                                    </label>
                                    <input
                                        type="file"
                                        id="fotoMascota"
                                        name="fotoMascota"
                                        accept="image/*"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-transparent"
                                    >
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
                    @endif

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

                <div id="imagePreviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/70 p-4">
                    <div class="relative w-full max-w-3xl rounded-2xl bg-white p-3 shadow-xl">
                        <button id="closeImagePreviewModal" type="button" class="absolute right-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-600 hover:bg-gray-100" aria-label="Cerrar vista previa">
                            ✕
                        </button>
                        <img id="imagePreviewModalImg" src="" alt="Vista previa" class="max-h-[80vh] w-full rounded-xl object-contain">
                    </div>
                </div>

    <script>
        const API_URL = '/api/adoptions';
        const STORE_URL = @json(route('adopciones.store'));
        const DELETE_URL_TEMPLATE = @json(route('adopciones.destroy', ['id' => '__ID__']));
        const UPDATE_IMAGE_URL_TEMPLATE = @json(route('adopciones.image.update', ['id' => '__ID__']));
        const IS_AUTHENTICATED = @json(auth()->check());
        const IS_REFUGIO = @json(auth()->check() && auth()->user()->hasRole('refugio'));
        const CAN_MANAGE_ADOPTION_IMAGE = @json($canManageAdoptionImage);
        const CAN_DELETE_ADOPTION = @json($canDeleteAdoption);
        const CAN_REGISTER_ADOPTION = @json($canRegisterAdoption);
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const IMAGE_PREVIEW_MODAL = document.getElementById('imagePreviewModal');
        const IMAGE_PREVIEW_MODAL_IMG = document.getElementById('imagePreviewModalImg');
        const CLOSE_IMAGE_PREVIEW_MODAL = document.getElementById('closeImagePreviewModal');

        function openImagePreview(imageUrl, imageAlt = 'Vista previa') {
            if (!IMAGE_PREVIEW_MODAL || !IMAGE_PREVIEW_MODAL_IMG || !imageUrl) {
                return;
            }

            IMAGE_PREVIEW_MODAL_IMG.src = imageUrl;
            IMAGE_PREVIEW_MODAL_IMG.alt = imageAlt;
            IMAGE_PREVIEW_MODAL.classList.remove('hidden');
            IMAGE_PREVIEW_MODAL.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeImagePreview() {
            if (!IMAGE_PREVIEW_MODAL || !IMAGE_PREVIEW_MODAL_IMG) {
                return;
            }

            IMAGE_PREVIEW_MODAL.classList.add('hidden');
            IMAGE_PREVIEW_MODAL.classList.remove('flex');
            IMAGE_PREVIEW_MODAL_IMG.src = '';
            document.body.classList.remove('overflow-hidden');
        }

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

        function setupFormAccess() {
            const form = document.getElementById('adopcionForm');
            const notice = document.getElementById('formAccessNotice');

            if (!form || !notice) {
                return;
            }

            if (IS_AUTHENTICATED) {
                notice.classList.add('hidden');
                form.querySelectorAll('input, select, textarea, button').forEach((element) => {
                    element.disabled = false;
                });
                return;
            }

            notice.textContent = 'Inicia sesión para poder registrar mascotas en adopción.';
            notice.className = 'mb-4 rounded-lg p-3 text-sm font-medium bg-amber-50 text-amber-800 border border-amber-200';
            notice.classList.remove('hidden');

            form.querySelectorAll('input, select, textarea, button').forEach((element) => {
                element.disabled = true;
            });
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
                        ? result.data.map((item) => ({
                            ...item,
                            id: item?.id || item?._docId || '',
                        }))
                        : Object.entries(result.data).map(([id, data]) => ({
                            ...data,
                            id: data?.id || data?._docId || id,
                        }));

                    adopcionesArray.sort((a, b) => new Date(b.fecha) - new Date(a.fecha));

                    if (adopcionesArray.length === 0) {
                        adopcionList.innerHTML = '<div class="text-center py-12"><p class="text-gray-500 text-lg">No hay adopciones registradas aún</p><p class="text-gray-400 text-sm mt-2">¡Sé el primero en registrar una mascota!</p></div>';
                        return;
                    }

                    adopcionesArray.forEach((adopcion) => {
                        const adoptionId = adopcion.id || adopcion._docId || '';
                        const fecha = new Date(adopcion.fecha).toLocaleDateString('es-ES');
                        const canDelete = Boolean(adoptionId) && CAN_DELETE_ADOPTION;
                        const canChangeImage = Boolean(adoptionId) && CAN_MANAGE_ADOPTION_IMAGE;
                        const html = `
                            <div class="group p-4 rounded-2xl border bg-teal-50 hover:shadow-md transition-all duration-200 cursor-pointer">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h3 class="text-lg font-extrabold text-gray-900">${adopcion.nombreAnimal}</h3>
                                        <p class="text-sm text-gray-600">${adopcion.tipoAnimal}</p>
                                    </div>
                                    ${CAN_MANAGE_ADOPTION_IMAGE || CAN_DELETE_ADOPTION ? `<div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            class="change-image inline-flex items-center justify-center rounded-full px-3 py-1.5 text-xs font-semibold border border-blue-200 bg-white text-blue-700 hover:bg-blue-50 transition ${canChangeImage ? '' : 'opacity-50 cursor-not-allowed'}"
                                            data-id="${adoptionId}"
                                            ${canChangeImage ? '' : 'disabled'}
                                        >
                                            Cambiar foto
                                        </button>
                                        ${CAN_DELETE_ADOPTION ? `<button
                                            type="button"
                                            class="delete-adopcion inline-flex items-center justify-center rounded-full px-3 py-1.5 text-xs font-semibold border border-red-200 bg-white text-red-700 hover:bg-red-50 transition ${canDelete ? '' : 'opacity-50 cursor-not-allowed'}"
                                            data-id="${adoptionId}"
                                            ${canDelete ? '' : 'disabled'}
                                        >
                                            Eliminar
                                        </button>` : ''}
                                    </div>` : ''}
                                </div>
                                <div class="mb-3 flex items-start gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="grid grid-cols-2 gap-3 mb-3">
                                            <div>
                                                <span class="text-sm text-gray-700"><strong>Edad:</strong> ${adopcion.edad} año${adopcion.edad != 1 ? 's' : ''}</span>
                                            </div>
                                            <div>
                                                <span class="text-sm text-gray-700"><strong>Raza:</strong> ${adopcion.raza}</span>
                                            </div>
                                        </div>
                                        ${adopcion.detalles ? `<div class="bg-white rounded-lg p-3 border">
                                            <p class="text-sm text-gray-600"><strong class="text-gray-900">Detalles:</strong> ${adopcion.detalles}</p>
                                        </div>` : ''}
                                    </div>
                                    ${adopcion.imageUrl ? `<div class="h-28 w-28 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-white sm:h-32 sm:w-32">
                                        <img src="${adopcion.imageUrl}" alt="Foto de ${adopcion.nombreAnimal}" class="preview-image h-full w-full cursor-zoom-in object-cover" data-full-image="${adopcion.imageUrl}" data-image-alt="Foto de ${adopcion.nombreAnimal}">
                                    </div>` : ''}
                                </div>
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
        setupFormAccess();
        loadAdopciones();

        // Recargar adopciones cada 3 segundos
        setInterval(loadAdopciones, 3000);

        if (CLOSE_IMAGE_PREVIEW_MODAL) {
            CLOSE_IMAGE_PREVIEW_MODAL.addEventListener('click', closeImagePreview);
        }

        if (IMAGE_PREVIEW_MODAL) {
            IMAGE_PREVIEW_MODAL.addEventListener('click', function (event) {
                if (event.target === IMAGE_PREVIEW_MODAL) {
                    closeImagePreview();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeImagePreview();
            }
        });

        // Eliminar adopción registrada
        document.getElementById('adopcionList').addEventListener('click', async function (event) {
            const previewImage = event.target.closest('.preview-image');
            if (previewImage) {
                openImagePreview(previewImage.dataset.fullImage || previewImage.src, previewImage.dataset.imageAlt || previewImage.alt || 'Vista previa');
                return;
            }

            const imageBtn = event.target.closest('.change-image');
            if (imageBtn && !imageBtn.disabled) {
                if (!IS_AUTHENTICATED) {
                    showAlert('Debes iniciar sesión para actualizar imágenes', 'error');
                    return;
                }

                if (!CAN_MANAGE_ADOPTION_IMAGE) {
                    showAlert('No tienes permisos para actualizar imágenes', 'error');
                    return;
                }

                const adoptionId = imageBtn.dataset.id;
                if (!adoptionId) {
                    showAlert('No se pudo identificar la adopción', 'error');
                    return;
                }

                const picker = document.createElement('input');
                picker.type = 'file';
                picker.accept = 'image/*';

                picker.addEventListener('change', async () => {
                    const file = picker.files?.[0];
                    if (!file) {
                        return;
                    }

                    const originalText = imageBtn.textContent;
                    imageBtn.disabled = true;
                    imageBtn.textContent = 'Subiendo...';

                    try {
                        const formData = new FormData();
                        formData.append('fotoMascota', file);

                        const updateUrl = UPDATE_IMAGE_URL_TEMPLATE.replace('__ID__', encodeURIComponent(adoptionId));
                        const response = await fetch(updateUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN || '',
                            },
                            body: formData,
                        });

                        const result = await response.json().catch(() => ({}));
                        if (!response.ok || !result.success) {
                            showAlert('Error: ' + (result.message || 'No se pudo actualizar la imagen'), 'error');
                            return;
                        }

                        showAlert('Imagen actualizada correctamente', 'success');
                        loadAdopciones();
                    } catch (error) {
                        showAlert('Error: ' + error.message, 'error');
                    } finally {
                        imageBtn.disabled = false;
                        imageBtn.textContent = originalText;
                    }
                });

                picker.click();
                return;
            }

            const btn = event.target.closest('.delete-adopcion');
            if (!btn || btn.disabled) return;

            if (!IS_AUTHENTICATED) {
                showAlert('Debes iniciar sesión para eliminar adopciones', 'error');
                return;
            }

            if (!CAN_DELETE_ADOPTION) {
                showAlert('No tienes permisos para eliminar adopciones', 'error');
                return;
            }

            const adoptionId = btn.dataset.id;
            if (!adoptionId) {
                showAlert('No se pudo identificar la adopción a eliminar', 'error');
                return;
            }

            if (!confirm('¿Seguro que quieres eliminar esta adopción?')) {
                return;
            }

            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Eliminando...';

            try {
                const deleteUrl = DELETE_URL_TEMPLATE.replace('__ID__', encodeURIComponent(adoptionId));
                const response = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN || '',
                    },
                });

                const result = await response.json().catch(() => ({}));

                if (response.ok && result.success) {
                    showAlert('Adopción eliminada correctamente', 'success');
                    loadAdopciones();
                } else {
                    showAlert('Error: ' + (result.message || 'No se pudo eliminar la adopción'), 'error');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'error');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });

        // Agregar evento para el formulario
        const adopcionForm = document.getElementById('adopcionForm');
        if (adopcionForm) {
            adopcionForm.addEventListener('submit', async function (event) {
                if (!CAN_REGISTER_ADOPTION) {
                    return;
                }

                event.preventDefault();

            if (!IS_AUTHENTICATED) {
                showAlert('Debes iniciar sesión para registrar una adopción', 'error');
                return;
            }

            const nombreAnimal = document.getElementById('nombreAnimal').value.trim();
            const tipoAnimal = document.getElementById('tipoAnimal').value;
            const edad = document.getElementById('edad').value;
            const raza = document.getElementById('raza').value.trim();
            const detalles = document.getElementById('detalles').value.trim();
            const fotoMascota = document.getElementById('fotoMascota').files[0];

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
                const formData = new FormData();
                formData.append('nombreAnimal', nombreAnimal);
                formData.append('tipoAnimal', tipoAnimal);
                formData.append('edad', edad);
                formData.append('raza', raza);
                formData.append('detalles', detalles || '');

                if (fotoMascota) {
                    formData.append('fotoMascota', fotoMascota);
                }

                const response = await fetch(STORE_URL, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN || '',
                    },
                    body: formData
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
        }
    </script>
</x-app-layout>
