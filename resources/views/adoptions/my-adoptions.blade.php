
@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-2 text-gray-800">Mis adopciones</h1>
    <p class="mb-6 text-gray-500">Aquí puedes ver las mascotas que registraste para adopción.</p>

    <div id="createAdoptionAlert" class="mb-4 hidden rounded-lg p-4 text-sm font-medium"></div>

    <div class="mb-8 flex justify-end">
        <button
            type="button"
            id="openCreateAdoptionModal"
            class="inline-flex min-h-[3.25rem] items-center justify-center rounded-full bg-teal-700 px-8 py-3 text-base font-extrabold tracking-wide text-white shadow-md shadow-teal-700/30 ring-2 ring-teal-300 transition hover:bg-teal-800 hover:shadow-lg focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-teal-300"
        >
            Registrar mascota en adopción
        </button>
    </div>

    <div class="mb-8 rounded-2xl border border-teal-100 bg-white p-4 shadow-sm">
        <p class="mb-3 text-sm font-semibold tracking-wide text-gray-700">Filtrar mascotas</p>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div>
                <label for="myAdoptionTypeFilter" class="mb-1 block text-sm font-medium text-gray-700">Tipo de mascota</label>
                <select
                    id="myAdoptionTypeFilter"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                >
                    <option value="">Todos los tipos</option>
                    <option value="perro">Perro</option>
                    <option value="gato">Gato</option>
                    <option value="conejo">Conejo</option>
                    <option value="ave">Ave</option>
                    <option value="otro">Otro</option>
                </select>
            </div>

            <div>
                <label for="myAdoptionSexFilter" class="mb-1 block text-sm font-medium text-gray-700">Sexo</label>
                <select
                    id="myAdoptionSexFilter"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                >
                    <option value="">Todos</option>
                    <option value="hembra">Hembra</option>
                    <option value="macho">Macho</option>
                </select>
            </div>

            <div class="flex items-end">
                <button
                    type="button"
                    id="clearMyAdoptionFilters"
                    class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100"
                >
                    Limpiar filtros
                </button>
            </div>
        </div>
    </div>

    <div id="myAdoptionsGrid" class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @forelse($adoptions as $item)
            @php
                $petName = (string) ($item['nombreAnimal'] ?? 'Mascota sin nombre');
                $petTypeRaw = strtolower(trim((string) ($item['tipoAnimal'] ?? '')));
                $petType = (string) ($item['tipoAnimal'] ?? 'Tipo no especificado');
                $sexValue = strtolower((string) ($item['sexo'] ?? ''));
                $sexLabel = $sexValue === 'hembra'
                    ? 'Hembra'
                    : ($sexValue === 'macho' ? 'Macho' : 'No especificado');
                $breed = (string) ($item['raza'] ?? 'No especificada');
                $details = (string) ($item['detalles'] ?? '');
                $age = isset($item['edad']) ? (int) $item['edad'] : null;
                $ageLabel = is_int($age) && $age >= 0
                    ? $age.' año'.($age === 1 ? '' : 's')
                    : 'N/D';
                $adoptionId = (string) ($item['id'] ?? $item['_docId'] ?? '');
                $hiddenValue = $item['isHidden'] ?? false;
                $isHidden = is_bool($hiddenValue)
                    ? $hiddenValue
                    : in_array(strtolower(trim((string) $hiddenValue)), ['1', 'true', 'yes', 'si', 'sí'], true);

                $createdAtLabel = 'Sin fecha';
                if (! empty($item['fecha'])) {
                    try {
                        $createdAtLabel = \Carbon\Carbon::parse((string) $item['fecha'])->format('d/m/Y');
                    } catch (\Throwable $e) {
                        $createdAtLabel = (string) $item['fecha'];
                    }
                }

                $imageUrl = ! empty($item['imageUrl']) ? (string) $item['imageUrl'] : null;
            @endphp

            <article
                class="my-adoption-card flex h-full flex-col rounded-2xl border border-teal-100 bg-teal-50 p-4 shadow-sm"
                data-type="{{ e($petTypeRaw) }}"
                data-type-label="{{ e($petType) }}"
                data-sexo="{{ e($sexValue) }}"
            >
                <div class="mb-3">
                    <h2 class="text-xl font-extrabold text-gray-900">{{ $petName }}</h2>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <p class="text-sm font-medium text-gray-600">{{ $petType }}</p>

                        @if($isHidden)
                            <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-slate-700">
                                Oculta por moderación
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mb-3 flex flex-1 items-start gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-lg border border-teal-100 bg-white px-3 py-2">
                                <span class="text-sm text-gray-700"><strong>Edad:</strong> {{ $ageLabel }}</span>
                            </div>

                            <div class="rounded-lg border border-teal-100 bg-white px-3 py-2">
                                <span class="text-sm text-gray-700"><strong>Raza:</strong> {{ $breed }}</span>
                            </div>

                            <div class="rounded-lg border border-teal-100 bg-white px-3 py-2 col-span-2 sm:col-span-1">
                                <span class="text-sm text-gray-700"><strong>Sexo:</strong> {{ $sexLabel }}</span>
                            </div>
                        </div>

                        @if($details !== '')
                            <div class="mt-3 rounded-lg border border-teal-100 bg-white p-3">
                                <p class="text-sm text-gray-600"><strong class="text-gray-900">Detalles:</strong> {{ $details }}</p>
                            </div>
                        @endif
                    </div>

                    @if($imageUrl)
                        <div class="shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-slate-100" style="width: clamp(5.5rem, 16vw, 7rem); height: clamp(5.5rem, 16vw, 7rem);">
                            <img
                                src="{{ $imageUrl }}"
                                alt="Foto de {{ $petName }}"
                                class="my-adoption-preview-image block h-full w-full cursor-zoom-in object-contain object-center p-1"
                                data-full-image="{{ $imageUrl }}"
                                data-image-alt="Foto de {{ $petName }}"
                                loading="lazy"
                                onerror="this.closest('div').innerHTML='<p class=&quot;px-2 text-center text-xs font-medium text-teal-800&quot;>Sin foto</p>'; this.closest('div').className='shrink-0 flex items-center justify-center rounded-xl border border-dashed border-teal-200 bg-teal-100/60';"
                            >
                        </div>
                    @else
                        <div class="shrink-0 flex items-center justify-center rounded-xl border border-dashed border-teal-200 bg-teal-100/60" style="width: clamp(5.5rem, 16vw, 7rem); height: clamp(5.5rem, 16vw, 7rem);">
                            <p class="px-2 text-center text-xs font-medium text-teal-800">Sin foto</p>
                        </div>
                    @endif
                </div>

                <div class="mt-auto flex items-center justify-between gap-3 border-t border-teal-200 pt-3">
                    <p class="text-xs text-gray-600">
                        Registrado el {{ $createdAtLabel }}
                        @if($isHidden)
                            | Publicación no visible para ciudadanos
                        @endif
                    </p>

                    @if($adoptionId !== '')
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                class="edit-my-adoption-btn inline-flex items-center justify-center rounded-full bg-teal-700 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-teal-800"
                                data-id="{{ e($adoptionId) }}"
                                data-nombre="{{ e($petName) }}"
                                data-tipo="{{ e($petType) }}"
                                data-sexo="{{ e($sexValue) }}"
                                data-edad="{{ is_int($age) && $age >= 0 ? $age : '' }}"
                                data-raza="{{ e($breed) }}"
                                data-detalles="{{ e($details) }}"
                            >
                                Editar
                            </button>

                            <button
                                type="button"
                                class="delete-my-adoption-btn inline-flex items-center justify-center rounded-full border border-red-300 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 transition hover:bg-red-100"
                                data-id="{{ e($adoptionId) }}"
                                data-name="{{ e($petName) }}"
                            >
                                Eliminar
                            </button>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="md:col-span-2 rounded-lg border bg-white p-4 text-gray-600">
                Aún no has registrado mascotas para adopción.
            </div>
        @endforelse
    </div>

    <div id="myAdoptionNoMatches" class="mt-4 hidden rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-800">
        No se encontraron mascotas con esos filtros.
    </div>

    <div id="createAdoptionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/70 p-3 sm:p-4">
        <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex items-start justify-between border-b border-slate-200 px-6 pb-4 pt-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Registro de mascota</h3>
                    <p class="mt-1 text-sm text-gray-600">Completa los datos para publicar una nueva adopción.</p>
                </div>
                <button id="closeCreateAdoptionModal" type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200" aria-label="Cerrar formulario">✕</button>
            </div>

            <form id="createAdoptionForm" class="space-y-4 px-6 pb-5 pt-4" enctype="multipart/form-data">
                <div>
                    <label for="createNombreAnimal" class="mb-1 block text-sm font-medium text-gray-700">Nombre del animal <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="createNombreAnimal"
                        name="nombreAnimal"
                        maxlength="255"
                        required
                        placeholder="Ej: Max, Luna..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                    >
                </div>

                <div>
                    <label for="createTipoAnimal" class="mb-1 block text-sm font-medium text-gray-700">Tipo de animal <span class="text-red-500">*</span></label>
                    <select
                        id="createTipoAnimal"
                        name="tipoAnimal"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
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
                    <label for="createSexo" class="mb-1 block text-sm font-medium text-gray-700">Sexo <span class="text-red-500">*</span></label>
                    <select
                        id="createSexo"
                        name="sexo"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                    >
                        <option value="">Selecciona una opción...</option>
                        <option value="hembra">Hembra</option>
                        <option value="macho">Macho</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="createEdad" class="mb-1 block text-sm font-medium text-gray-700">Edad (años) <span class="text-red-500">*</span></label>
                        <input
                            type="number"
                            id="createEdad"
                            name="edad"
                            min="0"
                            max="50"
                            required
                            placeholder="3"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                        >
                    </div>

                    <div>
                        <label for="createRaza" class="mb-1 block text-sm font-medium text-gray-700">Raza <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="createRaza"
                            name="raza"
                            maxlength="255"
                            required
                            placeholder="Ej: Golden Retriever..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                        >
                    </div>
                </div>

                <div>
                    <label for="createDetalles" class="mb-1 block text-sm font-medium text-gray-700">Detalles adicionales</label>
                    <textarea
                        id="createDetalles"
                        name="detalles"
                        rows="4"
                        maxlength="1000"
                        placeholder="Descripción del carácter, personalidad, necesidades especiales..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                    ></textarea>
                </div>

                <div>
                    <label for="createFotoMascota" class="mb-1 block text-sm font-medium text-gray-700">Foto de la mascota</label>
                    <input
                        type="file"
                        id="createFotoMascota"
                        name="fotoMascota"
                        accept="image/*"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                    >
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-200 pt-4">
                    <button type="button" id="cancelCreateAdoptionModal" class="rounded-full border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" id="createAdoptionSubmitBtn" class="rounded-full bg-[#F5E7DA] px-4 py-2 text-sm font-bold text-black hover:opacity-90">Registrar adopción</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editAdoptionModal" class="fixed inset-0 z-[55] hidden items-center justify-center bg-gray-900/70 p-3 sm:p-4">
        <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex items-start justify-between border-b border-slate-200 px-6 pb-4 pt-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Editar mascota registrada</h3>
                    <p class="mt-1 text-sm text-gray-600">Actualiza la información de la publicación.</p>
                </div>
                <button id="closeEditAdoptionModal" type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200" aria-label="Cerrar edición">✕</button>
            </div>

            <form id="editAdoptionForm" class="space-y-4 px-6 pb-5 pt-4" enctype="multipart/form-data">
                <input type="hidden" id="editAdoptionId" name="adoptionId">

                <div>
                    <label for="editNombreAnimal" class="mb-1 block text-sm font-medium text-gray-700">Nombre del animal <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="editNombreAnimal"
                        name="nombreAnimal"
                        maxlength="255"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                    >
                </div>

                <div>
                    <label for="editTipoAnimal" class="mb-1 block text-sm font-medium text-gray-700">Tipo de animal <span class="text-red-500">*</span></label>
                    <select
                        id="editTipoAnimal"
                        name="tipoAnimal"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
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
                    <label for="editSexo" class="mb-1 block text-sm font-medium text-gray-700">Sexo <span class="text-red-500">*</span></label>
                    <select
                        id="editSexo"
                        name="sexo"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                    >
                        <option value="">Selecciona una opción...</option>
                        <option value="hembra">Hembra</option>
                        <option value="macho">Macho</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="editEdad" class="mb-1 block text-sm font-medium text-gray-700">Edad (años) <span class="text-red-500">*</span></label>
                        <input
                            type="number"
                            id="editEdad"
                            name="edad"
                            min="0"
                            max="50"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                        >
                    </div>

                    <div>
                        <label for="editRaza" class="mb-1 block text-sm font-medium text-gray-700">Raza <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="editRaza"
                            name="raza"
                            maxlength="255"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                        >
                    </div>
                </div>

                <div>
                    <label for="editDetalles" class="mb-1 block text-sm font-medium text-gray-700">Detalles</label>
                    <textarea
                        id="editDetalles"
                        name="detalles"
                        rows="4"
                        maxlength="1000"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                    ></textarea>
                </div>

                <div>
                    <label for="editFotoMascota" class="mb-1 block text-sm font-medium text-gray-700">Nueva foto (opcional)</label>
                    <input
                        type="file"
                        id="editFotoMascota"
                        name="fotoMascota"
                        accept="image/*"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                    >
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-200 pt-4">
                    <button type="button" id="cancelEditAdoptionModal" class="rounded-full border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" id="editAdoptionSubmitBtn" class="rounded-full bg-teal-700 px-4 py-2 text-sm font-bold text-white hover:bg-teal-800">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteAdoptionConfirmModal" class="fixed inset-0 z-[58] hidden items-center justify-center bg-gray-900/70 p-3 sm:p-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex items-start justify-between border-b border-slate-200 px-6 pb-4 pt-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Eliminar publicación</h3>
                    <p class="mt-1 text-sm text-gray-600">Esta acción no se puede deshacer.</p>
                </div>
                <button id="closeDeleteAdoptionConfirmModal" type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200" aria-label="Cerrar confirmación">✕</button>
            </div>

            <div class="px-6 py-5">
                <p class="text-sm leading-relaxed text-gray-700">
                    ¿Seguro que quieres eliminar la publicación de <span id="deleteAdoptionTargetName" class="font-semibold text-gray-900">esta mascota</span>? También se eliminará su imagen asociada.
                </p>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-6 py-4">
                <button type="button" id="cancelDeleteAdoptionConfirmModal" class="rounded-full border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="button" id="confirmDeleteAdoptionBtn" class="rounded-full border border-red-300 bg-red-50 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-100">Eliminar</button>
            </div>
        </div>
    </div>

    <div id="imagePreviewModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-gray-900/75 p-4">
        <div class="relative w-full max-w-4xl rounded-2xl bg-white p-3 shadow-xl">
            <button id="closeImagePreviewModal" type="button" class="absolute right-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-600 hover:bg-gray-100" aria-label="Cerrar vista previa">✕</button>
            <img id="imagePreviewModalImg" src="" alt="Vista previa" class="max-h-[80vh] w-full rounded-xl object-contain">
        </div>
    </div>

    <script>
        const CREATE_ADOPTION_MODAL = document.getElementById('createAdoptionModal');
        const OPEN_CREATE_ADOPTION_MODAL = document.getElementById('openCreateAdoptionModal');
        const CLOSE_CREATE_ADOPTION_MODAL = document.getElementById('closeCreateAdoptionModal');
        const CANCEL_CREATE_ADOPTION_MODAL = document.getElementById('cancelCreateAdoptionModal');
        const CREATE_ADOPTION_FORM = document.getElementById('createAdoptionForm');
        const CREATE_ADOPTION_SUBMIT_BTN = document.getElementById('createAdoptionSubmitBtn');
        const CREATE_ADOPTION_ALERT = document.getElementById('createAdoptionAlert');
        const STORE_ADOPTION_URL = @json(route('adopciones.store'));
        const UPDATE_ADOPTION_URL_TEMPLATE = @json(route('adopciones.update', ['id' => '__ID__']));
        const DELETE_ADOPTION_URL_TEMPLATE = @json(route('adopciones.destroy', ['id' => '__ID__']));
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const IMAGE_PREVIEW_MODAL = document.getElementById('imagePreviewModal');
        const IMAGE_PREVIEW_MODAL_IMG = document.getElementById('imagePreviewModalImg');
        const CLOSE_IMAGE_PREVIEW_MODAL = document.getElementById('closeImagePreviewModal');
        const EDIT_ADOPTION_MODAL = document.getElementById('editAdoptionModal');
        const CLOSE_EDIT_ADOPTION_MODAL = document.getElementById('closeEditAdoptionModal');
        const CANCEL_EDIT_ADOPTION_MODAL = document.getElementById('cancelEditAdoptionModal');
        const EDIT_ADOPTION_FORM = document.getElementById('editAdoptionForm');
        const EDIT_ADOPTION_ID = document.getElementById('editAdoptionId');
        const EDIT_ADOPTION_SUBMIT_BTN = document.getElementById('editAdoptionSubmitBtn');
        const DELETE_ADOPTION_CONFIRM_MODAL = document.getElementById('deleteAdoptionConfirmModal');
        const CLOSE_DELETE_ADOPTION_CONFIRM_MODAL = document.getElementById('closeDeleteAdoptionConfirmModal');
        const CANCEL_DELETE_ADOPTION_CONFIRM_MODAL = document.getElementById('cancelDeleteAdoptionConfirmModal');
        const CONFIRM_DELETE_ADOPTION_BTN = document.getElementById('confirmDeleteAdoptionBtn');
        const DELETE_ADOPTION_TARGET_NAME = document.getElementById('deleteAdoptionTargetName');
        const MY_ADOPTION_TYPE_FILTER = document.getElementById('myAdoptionTypeFilter');
        const MY_ADOPTION_SEX_FILTER = document.getElementById('myAdoptionSexFilter');
        const CLEAR_MY_ADOPTION_FILTERS = document.getElementById('clearMyAdoptionFilters');
        const MY_ADOPTION_EMPTY_FILTER_STATE = document.getElementById('myAdoptionNoMatches');
        const MY_ADOPTION_CARDS = Array.from(document.querySelectorAll('.my-adoption-card'));

        let pendingDeleteAdoptionId = '';
        let pendingDeleteTriggerButton = null;
        let pendingDeleteTriggerButtonText = '';
        let pendingDeleteAdoptionName = '';

        function normalizeFilterValue(value) {
            return String(value || '').trim().toLowerCase();
        }

        function applyMyAdoptionsFilters() {
            if (MY_ADOPTION_CARDS.length === 0) {
                return;
            }

            const selectedType = normalizeFilterValue(MY_ADOPTION_TYPE_FILTER?.value);
            const selectedSex = normalizeFilterValue(MY_ADOPTION_SEX_FILTER?.value);

            let visibleCards = 0;
            MY_ADOPTION_CARDS.forEach((card) => {
                const cardType = normalizeFilterValue(card.dataset.type);
                const cardSex = normalizeFilterValue(card.dataset.sexo);

                const matchesType = !selectedType || cardType === selectedType;
                const matchesSex = !selectedSex || cardSex === selectedSex;
                const isVisible = matchesType && matchesSex;

                card.classList.toggle('hidden', !isVisible);
                if (isVisible) {
                    visibleCards += 1;
                }
            });

            if (MY_ADOPTION_EMPTY_FILTER_STATE) {
                MY_ADOPTION_EMPTY_FILTER_STATE.classList.toggle('hidden', visibleCards > 0);
            }
        }

        function clearMyAdoptionsFilters() {
            if (MY_ADOPTION_TYPE_FILTER) {
                MY_ADOPTION_TYPE_FILTER.value = '';
            }

            if (MY_ADOPTION_SEX_FILTER) {
                MY_ADOPTION_SEX_FILTER.value = '';
            }

            applyMyAdoptionsFilters();
        }

        function openEditAdoptionModal(adoptionData) {
            if (!EDIT_ADOPTION_MODAL || !EDIT_ADOPTION_FORM || !EDIT_ADOPTION_ID) {
                return;
            }

            EDIT_ADOPTION_FORM.reset();
            EDIT_ADOPTION_ID.value = adoptionData.id || '';
            document.getElementById('editNombreAnimal').value = adoptionData.nombre || '';
            document.getElementById('editTipoAnimal').value = adoptionData.tipo || '';
            document.getElementById('editSexo').value = adoptionData.sexo || '';
            document.getElementById('editEdad').value = adoptionData.edad || '';
            document.getElementById('editRaza').value = adoptionData.raza || '';
            document.getElementById('editDetalles').value = adoptionData.detalles || '';

            EDIT_ADOPTION_MODAL.classList.remove('hidden');
            EDIT_ADOPTION_MODAL.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeEditAdoptionModal() {
            if (!EDIT_ADOPTION_MODAL || !EDIT_ADOPTION_FORM || !EDIT_ADOPTION_ID) {
                return;
            }

            EDIT_ADOPTION_MODAL.classList.add('hidden');
            EDIT_ADOPTION_MODAL.classList.remove('flex');
            EDIT_ADOPTION_FORM.reset();
            EDIT_ADOPTION_ID.value = '';
            document.body.classList.remove('overflow-hidden');
        }

        function openDeleteAdoptionConfirmModal(adoptionId, triggerButton = null, adoptionName = '') {
            if (!DELETE_ADOPTION_CONFIRM_MODAL || !adoptionId) {
                return;
            }

            pendingDeleteAdoptionId = adoptionId;
            pendingDeleteTriggerButton = triggerButton;
            pendingDeleteTriggerButtonText = triggerButton?.textContent || '';
            pendingDeleteAdoptionName = String(adoptionName || '').trim() || 'esta mascota';

            if (DELETE_ADOPTION_TARGET_NAME) {
                DELETE_ADOPTION_TARGET_NAME.textContent = pendingDeleteAdoptionName;
            }

            DELETE_ADOPTION_CONFIRM_MODAL.classList.remove('hidden');
            DELETE_ADOPTION_CONFIRM_MODAL.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeDeleteAdoptionConfirmModal() {
            if (!DELETE_ADOPTION_CONFIRM_MODAL) {
                return;
            }

            DELETE_ADOPTION_CONFIRM_MODAL.classList.add('hidden');
            DELETE_ADOPTION_CONFIRM_MODAL.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');

            pendingDeleteAdoptionId = '';
            pendingDeleteTriggerButton = null;
            pendingDeleteTriggerButtonText = '';
            pendingDeleteAdoptionName = '';

            if (DELETE_ADOPTION_TARGET_NAME) {
                DELETE_ADOPTION_TARGET_NAME.textContent = 'esta mascota';
            }
        }

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

        function showCreateAdoptionAlert(message, type = 'success') {
            if (!CREATE_ADOPTION_ALERT) {
                return;
            }

            CREATE_ADOPTION_ALERT.textContent = message;
            CREATE_ADOPTION_ALERT.className = type === 'success'
                ? 'mb-4 rounded-lg p-4 text-sm font-medium bg-green-50 text-green-800 border border-green-200'
                : 'mb-4 rounded-lg p-4 text-sm font-medium bg-red-50 text-red-800 border border-red-200';
            CREATE_ADOPTION_ALERT.classList.remove('hidden');
        }

        function openCreateAdoptionModal() {
            if (!CREATE_ADOPTION_MODAL) {
                return;
            }

            CREATE_ADOPTION_MODAL.classList.remove('hidden');
            CREATE_ADOPTION_MODAL.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeCreateAdoptionModal() {
            if (!CREATE_ADOPTION_MODAL || !CREATE_ADOPTION_FORM) {
                return;
            }

            CREATE_ADOPTION_MODAL.classList.add('hidden');
            CREATE_ADOPTION_MODAL.classList.remove('flex');
            CREATE_ADOPTION_FORM.reset();
            document.body.classList.remove('overflow-hidden');
        }

        if (OPEN_CREATE_ADOPTION_MODAL) {
            OPEN_CREATE_ADOPTION_MODAL.addEventListener('click', openCreateAdoptionModal);
        }

        if (CLOSE_CREATE_ADOPTION_MODAL) {
            CLOSE_CREATE_ADOPTION_MODAL.addEventListener('click', closeCreateAdoptionModal);
        }

        if (CANCEL_CREATE_ADOPTION_MODAL) {
            CANCEL_CREATE_ADOPTION_MODAL.addEventListener('click', closeCreateAdoptionModal);
        }

        if (CREATE_ADOPTION_MODAL) {
            CREATE_ADOPTION_MODAL.addEventListener('click', function (event) {
                if (event.target === CREATE_ADOPTION_MODAL) {
                    closeCreateAdoptionModal();
                }
            });
        }

        if (CLOSE_EDIT_ADOPTION_MODAL) {
            CLOSE_EDIT_ADOPTION_MODAL.addEventListener('click', closeEditAdoptionModal);
        }

        if (CANCEL_EDIT_ADOPTION_MODAL) {
            CANCEL_EDIT_ADOPTION_MODAL.addEventListener('click', closeEditAdoptionModal);
        }

        if (EDIT_ADOPTION_MODAL) {
            EDIT_ADOPTION_MODAL.addEventListener('click', function (event) {
                if (event.target === EDIT_ADOPTION_MODAL) {
                    closeEditAdoptionModal();
                }
            });
        }

        if (CLOSE_DELETE_ADOPTION_CONFIRM_MODAL) {
            CLOSE_DELETE_ADOPTION_CONFIRM_MODAL.addEventListener('click', closeDeleteAdoptionConfirmModal);
        }

        if (CANCEL_DELETE_ADOPTION_CONFIRM_MODAL) {
            CANCEL_DELETE_ADOPTION_CONFIRM_MODAL.addEventListener('click', closeDeleteAdoptionConfirmModal);
        }

        if (DELETE_ADOPTION_CONFIRM_MODAL) {
            DELETE_ADOPTION_CONFIRM_MODAL.addEventListener('click', function (event) {
                if (event.target === DELETE_ADOPTION_CONFIRM_MODAL) {
                    closeDeleteAdoptionConfirmModal();
                }
            });
        }

        if (MY_ADOPTION_TYPE_FILTER) {
            MY_ADOPTION_TYPE_FILTER.addEventListener('change', applyMyAdoptionsFilters);
        }

        if (MY_ADOPTION_SEX_FILTER) {
            MY_ADOPTION_SEX_FILTER.addEventListener('change', applyMyAdoptionsFilters);
        }

        if (CLEAR_MY_ADOPTION_FILTERS) {
            CLEAR_MY_ADOPTION_FILTERS.addEventListener('click', clearMyAdoptionsFilters);
        }

        applyMyAdoptionsFilters();

        document.querySelectorAll('.my-adoption-preview-image').forEach((imageElement) => {
            imageElement.addEventListener('click', function () {
                openImagePreview(
                    this.dataset.fullImage || this.src,
                    this.dataset.imageAlt || this.alt || 'Vista previa'
                );
            });
        });

        document.querySelectorAll('.edit-my-adoption-btn').forEach((button) => {
            button.addEventListener('click', function () {
                openEditAdoptionModal({
                    id: this.dataset.id,
                    nombre: this.dataset.nombre,
                    tipo: this.dataset.tipo,
                    sexo: this.dataset.sexo,
                    edad: this.dataset.edad,
                    raza: this.dataset.raza,
                    detalles: this.dataset.detalles,
                });
            });
        });

        document.querySelectorAll('.delete-my-adoption-btn').forEach((button) => {
            button.addEventListener('click', function () {
                const adoptionId = this.dataset.id;
                if (!adoptionId) {
                    showCreateAdoptionAlert('No se pudo identificar la publicación.', 'error');
                    return;
                }

                openDeleteAdoptionConfirmModal(adoptionId, this, this.dataset.name || '');
            });
        });

        if (CONFIRM_DELETE_ADOPTION_BTN) {
            CONFIRM_DELETE_ADOPTION_BTN.addEventListener('click', async function () {
                const adoptionId = pendingDeleteAdoptionId;
                const triggerButton = pendingDeleteTriggerButton;

                if (!adoptionId) {
                    closeDeleteAdoptionConfirmModal();
                    return;
                }

                const confirmOriginalText = this.textContent;
                this.disabled = true;
                this.textContent = 'Eliminando...';

                if (triggerButton) {
                    triggerButton.disabled = true;
                    triggerButton.textContent = 'Eliminando...';
                }

                try {
                    const deleteUrl = DELETE_ADOPTION_URL_TEMPLATE.replace('__ID__', encodeURIComponent(adoptionId));
                    const response = await fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                        },
                    });

                    const result = await response.json().catch(() => ({}));
                    if (!response.ok || !result.success) {
                        showCreateAdoptionAlert(result.message || 'No se pudo eliminar la publicación.', 'error');
                        if (triggerButton) {
                            triggerButton.disabled = false;
                            triggerButton.textContent = pendingDeleteTriggerButtonText || 'Eliminar';
                        }
                        closeDeleteAdoptionConfirmModal();
                        return;
                    }

                    showCreateAdoptionAlert('Publicación eliminada correctamente.', 'success');
                    closeDeleteAdoptionConfirmModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 400);
                } catch (error) {
                    showCreateAdoptionAlert('Error al eliminar la publicación: ' + error.message, 'error');
                    if (triggerButton) {
                        triggerButton.disabled = false;
                        triggerButton.textContent = pendingDeleteTriggerButtonText || 'Eliminar';
                    }
                    closeDeleteAdoptionConfirmModal();
                } finally {
                    this.disabled = false;
                    this.textContent = confirmOriginalText;
                }
            });
        }

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

        if (CREATE_ADOPTION_FORM) {
            CREATE_ADOPTION_FORM.addEventListener('submit', async function (event) {
                event.preventDefault();

                const originalText = CREATE_ADOPTION_SUBMIT_BTN?.textContent || 'Registrar adopción';
                if (CREATE_ADOPTION_SUBMIT_BTN) {
                    CREATE_ADOPTION_SUBMIT_BTN.disabled = true;
                    CREATE_ADOPTION_SUBMIT_BTN.textContent = 'Registrando...';
                }

                try {
                    const formData = new FormData(CREATE_ADOPTION_FORM);
                    const response = await fetch(STORE_ADOPTION_URL, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                        },
                        body: formData,
                    });

                    const result = await response.json().catch(() => ({}));

                    if (!response.ok || !result.success) {
                        const validationErrors = result.errors && typeof result.errors === 'object'
                            ? Object.values(result.errors).flat().join(' ')
                            : '';
                        const message = validationErrors || result.message || 'No se pudo registrar la adopción.';
                        showCreateAdoptionAlert(message, 'error');
                        return;
                    }

                    closeCreateAdoptionModal();
                    showCreateAdoptionAlert('Mascota registrada correctamente en adopción.', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } catch (error) {
                    showCreateAdoptionAlert('Error al registrar la mascota: ' + error.message, 'error');
                } finally {
                    if (CREATE_ADOPTION_SUBMIT_BTN) {
                        CREATE_ADOPTION_SUBMIT_BTN.disabled = false;
                        CREATE_ADOPTION_SUBMIT_BTN.textContent = originalText;
                    }
                }
            });
        }

        if (EDIT_ADOPTION_FORM) {
            EDIT_ADOPTION_FORM.addEventListener('submit', async function (event) {
                event.preventDefault();

                const adoptionId = EDIT_ADOPTION_ID?.value || '';
                if (!adoptionId) {
                    showCreateAdoptionAlert('No se pudo identificar la publicación a editar.', 'error');
                    return;
                }

                const nombreAnimal = document.getElementById('editNombreAnimal')?.value?.trim() || '';
                const tipoAnimal = document.getElementById('editTipoAnimal')?.value || '';
                const sexo = document.getElementById('editSexo')?.value || '';
                const edad = document.getElementById('editEdad')?.value || '';
                const raza = document.getElementById('editRaza')?.value?.trim() || '';
                const detalles = document.getElementById('editDetalles')?.value?.trim() || '';

                if (!nombreAnimal || !tipoAnimal || !sexo || !edad || !raza) {
                    showCreateAdoptionAlert('Completa todos los campos obligatorios para editar.', 'error');
                    return;
                }

                if (Number(edad) < 0 || Number(edad) > 50) {
                    showCreateAdoptionAlert('Ingresa una edad válida entre 0 y 50.', 'error');
                    return;
                }

                const originalText = EDIT_ADOPTION_SUBMIT_BTN?.textContent || 'Guardar cambios';
                if (EDIT_ADOPTION_SUBMIT_BTN) {
                    EDIT_ADOPTION_SUBMIT_BTN.disabled = true;
                    EDIT_ADOPTION_SUBMIT_BTN.textContent = 'Guardando...';
                }

                try {
                    const formData = new FormData(EDIT_ADOPTION_FORM);
                    formData.append('_method', 'PATCH');

                    const updateUrl = UPDATE_ADOPTION_URL_TEMPLATE.replace('__ID__', encodeURIComponent(adoptionId));
                    const response = await fetch(updateUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                        },
                        body: formData,
                    });

                    const result = await response.json().catch(() => ({}));
                    if (!response.ok || !result.success) {
                        const validationErrors = result.errors && typeof result.errors === 'object'
                            ? Object.values(result.errors).flat().join(' ')
                            : '';
                        const message = validationErrors || result.message || 'No se pudo actualizar la publicación.';
                        showCreateAdoptionAlert(message, 'error');
                        return;
                    }

                    closeEditAdoptionModal();
                    showCreateAdoptionAlert('Publicación actualizada correctamente.', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 400);
                } catch (error) {
                    showCreateAdoptionAlert('Error al actualizar la publicación: ' + error.message, 'error');
                } finally {
                    if (EDIT_ADOPTION_SUBMIT_BTN) {
                        EDIT_ADOPTION_SUBMIT_BTN.disabled = false;
                        EDIT_ADOPTION_SUBMIT_BTN.textContent = originalText;
                    }
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeCreateAdoptionModal();
                closeEditAdoptionModal();
                closeDeleteAdoptionConfirmModal();
                closeImagePreview();
            }
        });
    </script>
@endsection
