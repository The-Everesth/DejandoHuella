<x-app-layout>
    @php($canRegisterAdoption = false)
    @php($canDeleteAdoption = false)
    @php($canRequestAdoption = auth()->check() && auth()->user()->hasRole('ciudadano'))
    @php($canManageAllAdoptions = auth()->check() && auth()->user()->hasRole('admin'))
    @php($currentUserId = auth()->id())

    <x-slot name="header">
        <h1 class="text-3xl font-bold text-gray-900">Adopción de Mascotas</h1>
        <p class="text-gray-600 mt-2">Explora mascotas disponibles y encuentra a tu próximo compañero.</p>
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
                                    <label for="sexo" class="block text-sm font-medium text-gray-700 mb-1">
                                        Sexo <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="sexo"
                                        name="sexo"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-700 focus:border-transparent"
                                    >
                                        <option value="">Selecciona una opción...</option>
                                        <option value="hembra">Hembra</option>
                                        <option value="macho">Macho</option>
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
                            <div id="adopcionList" class="grid grid-cols-1 gap-4 md:grid-cols-2 max-h-[34rem] overflow-y-auto pr-2">
                                <div class="col-span-full text-center py-12">
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

                @if($canRegisterAdoption)
                <div id="editAdoptionModal" class="fixed inset-0 z-[55] hidden items-center justify-center bg-gray-900/70 p-3 sm:p-4">
                    <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl">
                        <div class="flex items-start justify-between border-b border-slate-200 px-6 pb-4 pt-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Editar mascota registrada</h3>
                                <p class="mt-1 text-sm text-gray-600">Actualiza los datos de la mascota y guarda los cambios.</p>
                            </div>
                            <button id="closeEditAdoptionModal" type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200" aria-label="Cerrar edición">✕</button>
                        </div>

                        <form id="editAdoptionForm" class="space-y-4 px-6 pb-5 pt-4">
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
                                <button type="submit" id="editAdoptionSubmitBtn" class="rounded-full bg-[#F5E7DA] px-4 py-2 text-sm font-bold text-black hover:opacity-90">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                @if($canRequestAdoption)
                <div id="adoptionRequestModal" class="fixed inset-0 z-50 hidden items-stretch justify-center bg-gray-900/70 p-3 sm:p-4">
                    <div class="my-2 flex h-full w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl sm:my-4">
                        <div class="flex items-start justify-between border-b border-slate-200 px-6 pb-4 pt-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Formulario de adopción</h3>
                                <p class="mt-1 text-sm text-gray-600">Completa tu solicitud para <span id="adoptionRequestAdoptionName" class="font-semibold text-gray-900"></span>.</p>
                            </div>
                            <button id="closeAdoptionRequestModal" type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200" aria-label="Cerrar formulario">✕</button>
                        </div>

                        <form id="adoptionRequestForm" class="flex-1 space-y-4 overflow-y-auto px-6 pb-4 pt-4">
                            <input type="hidden" id="adoptionRequestAdoptionId" name="adoptionId">

                            <div>
                                <label for="solicitudNombreCompleto" class="mb-1 block text-sm font-medium text-gray-700">Nombre completo <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    id="solicitudNombreCompleto"
                                    name="nombreCompleto"
                                    maxlength="255"
                                    required
                                    placeholder="Ej: Ana María Pérez"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                                >
                            </div>

                            <div>
                                <label for="solicitudDireccionCiudad" class="mb-1 block text-sm font-medium text-gray-700">Dirección / ciudad <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    id="solicitudDireccionCiudad"
                                    name="direccionCiudad"
                                    maxlength="255"
                                    required
                                    placeholder="Ej: Medellín, Barrio Laureles"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                                >
                            </div>

                            <div>
                                <label for="solicitudTipoVivienda" class="mb-1 block text-sm font-medium text-gray-700">Tipo de vivienda <span class="text-red-500">*</span></label>
                                <select
                                    id="solicitudTipoVivienda"
                                    name="tipoVivienda"
                                    required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                                >
                                    <option value="">Selecciona una opción...</option>
                                    <option value="casa">Casa</option>
                                    <option value="apartamento">Apartamento</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <div>
                                <p class="mb-1 block text-sm font-medium text-gray-700">¿Tienes un patio o jardín? <span class="text-red-500">*</span></p>
                                <div class="flex flex-wrap gap-4">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio" name="patioJardin" value="si" class="text-teal-700 focus:ring-teal-700" required>
                                        <span>Sí</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio" name="patioJardin" value="no" class="text-teal-700 focus:ring-teal-700" required>
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <p class="mb-1 block text-sm font-medium text-gray-700">¿Quiénes viven en tu hogar? <span class="text-red-500">*</span></p>
                                <p class="mb-2 text-xs text-gray-500">Marca todas las opciones que apliquen</p>
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="hogarIntegrantes[]" value="adultos" class="rounded border-gray-300 text-teal-700 focus:ring-teal-700">
                                        <span>Adultos</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="hogarIntegrantes[]" value="ninos" class="rounded border-gray-300 text-teal-700 focus:ring-teal-700">
                                        <span>Niños</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="hogarIntegrantes[]" value="movilidad_reducida" class="rounded border-gray-300 text-teal-700 focus:ring-teal-700">
                                        <span>Personas con movilidad reducida</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="hogarIntegrantes[]" value="otros" class="rounded border-gray-300 text-teal-700 focus:ring-teal-700">
                                        <span>Otros</span>
                                    </label>
                                </div>
                                <div id="solicitudHogarOtrosWrap" class="mt-2 hidden">
                                    <label for="solicitudHogarOtros" class="mb-1 block text-sm font-medium text-gray-700">Si marcaste "Otros", especifica <span class="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        id="solicitudHogarOtros"
                                        name="hogarIntegrantesOtros"
                                        maxlength="255"
                                        placeholder="Ej: adulto mayor"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                                    >
                                </div>
                            </div>

                            <div>
                                <p class="mb-1 block text-sm font-medium text-gray-700">¿Tienes otros animales en casa? <span class="text-red-500">*</span></p>
                                <div class="flex flex-wrap gap-4">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio" name="tieneOtrosAnimales" value="si" class="text-teal-700 focus:ring-teal-700" required>
                                        <span>Sí</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio" name="tieneOtrosAnimales" value="no" class="text-teal-700 focus:ring-teal-700" required>
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>

                            <div id="solicitudOtrosAnimalesTipoWrap" class="hidden">
                                <label for="solicitudTiposOtrosAnimales" class="mb-1 block text-sm font-medium text-gray-700">Si tienes otros animales, ¿qué tipo? <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    id="solicitudTiposOtrosAnimales"
                                    name="tiposOtrosAnimales"
                                    maxlength="255"
                                    placeholder="Ej: perro, gato, pájaro"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                                >
                            </div>

                            <div id="solicitudOtrosAnimalesEsterWrap" class="hidden">
                                <p class="mb-1 block text-sm font-medium text-gray-700">¿Tus otros animales están castrados o esterilizados? <span class="text-red-500">*</span></p>
                                <div class="flex flex-wrap gap-4">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio" name="otrosAnimalesEsterilizados" value="si" class="text-teal-700 focus:ring-teal-700">
                                        <span>Sí</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio" name="otrosAnimalesEsterilizados" value="no" class="text-teal-700 focus:ring-teal-700">
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <p class="mb-1 block text-sm font-medium text-gray-700">¿Has tenido mascotas antes? <span class="text-red-500">*</span></p>
                                <div class="flex flex-wrap gap-4">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio" name="tuvoMascotasAntes" value="si" class="text-teal-700 focus:ring-teal-700" required>
                                        <span>Sí</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio" name="tuvoMascotasAntes" value="no" class="text-teal-700 focus:ring-teal-700" required>
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>

                            <div id="solicitudMascotasAnterioresWrap" class="hidden">
                                <label for="solicitudDetalleMascotasAnteriores" class="mb-1 block text-sm font-medium text-gray-700">Si respondiste sí, ¿qué tipo de mascotas y por cuánto tiempo? <span class="text-red-500">*</span></label>
                                <textarea
                                    id="solicitudDetalleMascotasAnteriores"
                                    name="detalleMascotasAnteriores"
                                    rows="3"
                                    maxlength="2000"
                                    placeholder="Ej: Perro durante 8 años, gato durante 3 años"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                                ></textarea>
                            </div>

                            <div>
                                <p class="mb-1 block text-sm font-medium text-gray-700">¿Estás dispuesto a proporcionar atención veterinaria regular? <span class="text-red-500">*</span></p>
                                <div class="flex flex-wrap gap-4">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio" name="dispuestoAtencionVeterinaria" value="si" class="text-teal-700 focus:ring-teal-700" required>
                                        <span>Sí</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="radio" name="dispuestoAtencionVeterinaria" value="no" class="text-teal-700 focus:ring-teal-700" required>
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label for="solicitudExperienciaMascotas" class="mb-1 block text-sm font-medium text-gray-700">Experiencia previa con mascotas <span class="text-red-500">*</span></label>
                                <textarea
                                    id="solicitudExperienciaMascotas"
                                    name="experienciaMascotas"
                                    rows="3"
                                    maxlength="2000"
                                    required
                                    placeholder="Cuéntanos tu experiencia cuidando mascotas"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                                ></textarea>
                            </div>

                            <div>
                                <label for="solicitudTelefono" class="mb-1 block text-sm font-medium text-gray-700">Teléfono de contacto <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    id="solicitudTelefono"
                                    name="telefono"
                                    maxlength="40"
                                    required
                                    placeholder="Ej: 3001234567"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                                >
                            </div>

                            <div>
                                <label for="solicitudMensaje" class="mb-1 block text-sm font-medium text-gray-700">¿Por qué deseas adoptarlo? <span class="text-red-500">*</span></label>
                                <textarea
                                    id="solicitudMensaje"
                                    name="mensaje"
                                    rows="4"
                                    maxlength="2000"
                                    required
                                    placeholder="Cuéntanos brevemente sobre tu experiencia y hogar para la mascota"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                                ></textarea>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-white p-3">
                                <label for="solicitudConfirmacion" class="inline-flex items-start gap-2 text-sm text-slate-700">
                                    <input
                                        type="checkbox"
                                        id="solicitudConfirmacion"
                                        name="confirmacionRespuestas"
                                        class="mt-0.5 rounded border-gray-300 text-teal-700 focus:ring-teal-700"
                                    >
                                    <span>Confirmo que mis respuestas son correctas y autorizo su uso para evaluar mi solicitud de adopción.</span>
                                </label>
                            </div>

                            <div class="sticky bottom-0 -mx-6 mt-2 flex items-center justify-end gap-2 border-t border-slate-200 bg-white px-6 pb-1 pt-3">
                                <button type="button" id="cancelAdoptionRequest" class="rounded-full border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</button>
                                <button type="submit" id="adoptionRequestSubmitBtn" class="rounded-full bg-[#F5E7DA] px-4 py-2 text-sm font-bold text-black hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50" disabled aria-disabled="true">Enviar solicitud</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="adoptionRequestSuccessModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-gray-900/70 p-4">
                    <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl">
                        <div class="mb-2 flex justify-end">
                            <button id="closeAdoptionRequestSuccessModal" type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200" aria-label="Cerrar confirmación">✕</button>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">¡Gracias por tu solicitud!</h3>
                        <p id="adoptionRequestSuccessMessage" class="mt-3 text-sm leading-relaxed text-gray-700">
                            Hemos recibido tu solicitud y estamos revisando tu información. Nos pondremos en contacto contigo pronto para continuar con el proceso de adopción. ¡Gracias por elegir darle un hogar amoroso a una de nuestras mascotas!
                        </p>
                        <div class="mt-6 flex justify-end">
                            <button id="confirmAdoptionRequestSuccessModal" type="button" class="rounded-full bg-[#F5E7DA] px-5 py-2 text-sm font-bold text-black hover:opacity-90">Entendido</button>
                        </div>
                    </div>
                </div>
                @endif

    <script>
        const API_URL = '/api/adoptions';
        const STORE_URL = @json(route('adopciones.store'));
        const DELETE_URL_TEMPLATE = @json(route('adopciones.destroy', ['id' => '__ID__']));
        const REQUEST_URL_TEMPLATE = @json(route('adopciones.request.store', ['id' => '__ID__']));
        const MY_REQUESTED_ADOPTIONS_URL = @json(route('my.requested.adoptions'));
        const UPDATE_ADOPTION_URL_TEMPLATE = @json(route('adopciones.update', ['id' => '__ID__']));
        const IS_AUTHENTICATED = @json(auth()->check());
        const IS_REFUGIO = @json(auth()->check() && auth()->user()->hasRole('refugio'));
        const CURRENT_USER_ID = @json($currentUserId);
        const CAN_MANAGE_ALL_ADOPTIONS = @json($canManageAllAdoptions);
        const CAN_DELETE_ADOPTION = @json($canDeleteAdoption);
        const CAN_REGISTER_ADOPTION = @json($canRegisterAdoption);
        const CAN_REQUEST_ADOPTION = @json($canRequestAdoption);
        let requestedAdoptionIds = new Set();
        let requestedAdoptionIdsLoaded = false;
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const IMAGE_PREVIEW_MODAL = document.getElementById('imagePreviewModal');
        const IMAGE_PREVIEW_MODAL_IMG = document.getElementById('imagePreviewModalImg');
        const CLOSE_IMAGE_PREVIEW_MODAL = document.getElementById('closeImagePreviewModal');
        const ADOPTION_REQUEST_MODAL = document.getElementById('adoptionRequestModal');
        const ADOPTION_REQUEST_SUCCESS_MODAL = document.getElementById('adoptionRequestSuccessModal');
        const CLOSE_ADOPTION_REQUEST_MODAL = document.getElementById('closeAdoptionRequestModal');
        const CLOSE_ADOPTION_REQUEST_SUCCESS_MODAL = document.getElementById('closeAdoptionRequestSuccessModal');
        const CONFIRM_ADOPTION_REQUEST_SUCCESS_MODAL = document.getElementById('confirmAdoptionRequestSuccessModal');
        const CANCEL_ADOPTION_REQUEST_MODAL = document.getElementById('cancelAdoptionRequest');
        const ADOPTION_REQUEST_FORM = document.getElementById('adoptionRequestForm');
        const ADOPTION_REQUEST_ADOPTION_ID = document.getElementById('adoptionRequestAdoptionId');
        const ADOPTION_REQUEST_ADOPTION_NAME = document.getElementById('adoptionRequestAdoptionName');
        const ADOPTION_REQUEST_SUCCESS_MESSAGE = document.getElementById('adoptionRequestSuccessMessage');
        const ADOPTION_REQUEST_CONFIRMATION = document.getElementById('solicitudConfirmacion');
        const ADOPTION_REQUEST_SUBMIT_BTN = document.getElementById('adoptionRequestSubmitBtn');
        const ADOPTION_REQUEST_SUCCESS_TEXT = 'Hemos recibido tu solicitud y estamos revisando tu información. Nos pondremos en contacto contigo pronto para continuar con el proceso de adopción. ¡Gracias por elegir darle un hogar amoroso a una de nuestras mascotas!';
        const EDIT_ADOPTION_MODAL = document.getElementById('editAdoptionModal');
        const CLOSE_EDIT_ADOPTION_MODAL = document.getElementById('closeEditAdoptionModal');
        const CANCEL_EDIT_ADOPTION_MODAL = document.getElementById('cancelEditAdoptionModal');
        const EDIT_ADOPTION_FORM = document.getElementById('editAdoptionForm');
        const EDIT_ADOPTION_ID = document.getElementById('editAdoptionId');
        const EDIT_ADOPTION_SUBMIT_BTN = document.getElementById('editAdoptionSubmitBtn');

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

        function openAdoptionRequestModal(adoptionId, adoptionName) {
            if (!ADOPTION_REQUEST_MODAL || !ADOPTION_REQUEST_ADOPTION_ID || !ADOPTION_REQUEST_ADOPTION_NAME) {
                return;
            }

            ADOPTION_REQUEST_ADOPTION_ID.value = adoptionId;
            ADOPTION_REQUEST_ADOPTION_NAME.textContent = adoptionName || 'esta mascota';
            ADOPTION_REQUEST_MODAL.classList.remove('hidden');
            ADOPTION_REQUEST_MODAL.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            updateAdoptionRequestConditionalFields();
            updateAdoptionRequestSubmitState();
        }

        function closeAdoptionRequestModal() {
            if (!ADOPTION_REQUEST_MODAL || !ADOPTION_REQUEST_FORM) {
                return;
            }

            ADOPTION_REQUEST_MODAL.classList.add('hidden');
            ADOPTION_REQUEST_MODAL.classList.remove('flex');
            ADOPTION_REQUEST_FORM.reset();
            document.body.classList.remove('overflow-hidden');
            updateAdoptionRequestConditionalFields();
            updateAdoptionRequestSubmitState();
        }

        function openAdoptionRequestSuccessModal() {
            if (!ADOPTION_REQUEST_SUCCESS_MODAL) {
                return;
            }

            if (ADOPTION_REQUEST_SUCCESS_MESSAGE) {
                ADOPTION_REQUEST_SUCCESS_MESSAGE.textContent = ADOPTION_REQUEST_SUCCESS_TEXT;
            }

            ADOPTION_REQUEST_SUCCESS_MODAL.classList.remove('hidden');
            ADOPTION_REQUEST_SUCCESS_MODAL.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeAdoptionRequestSuccessModal() {
            if (!ADOPTION_REQUEST_SUCCESS_MODAL) {
                return;
            }

            ADOPTION_REQUEST_SUCCESS_MODAL.classList.add('hidden');
            ADOPTION_REQUEST_SUCCESS_MODAL.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function updateAdoptionRequestSubmitState() {
            if (!ADOPTION_REQUEST_SUBMIT_BTN) {
                return;
            }

            const confirmed = Boolean(ADOPTION_REQUEST_CONFIRMATION?.checked);
            ADOPTION_REQUEST_SUBMIT_BTN.disabled = !confirmed;
            ADOPTION_REQUEST_SUBMIT_BTN.setAttribute('aria-disabled', confirmed ? 'false' : 'true');
        }

        function setConditionalVisibility(elementId, shouldShow) {
            const element = document.getElementById(elementId);
            if (!element) {
                return;
            }

            if (shouldShow) {
                element.classList.remove('hidden');
                return;
            }

            element.classList.add('hidden');
        }

        function updateAdoptionRequestConditionalFields() {
            const hogarOtrosSelected = document.querySelector('input[name="hogarIntegrantes[]"][value="otros"]')?.checked;
            setConditionalVisibility('solicitudHogarOtrosWrap', Boolean(hogarOtrosSelected));

            const tieneOtrosAnimales = document.querySelector('input[name="tieneOtrosAnimales"]:checked')?.value === 'si';
            setConditionalVisibility('solicitudOtrosAnimalesTipoWrap', tieneOtrosAnimales);
            setConditionalVisibility('solicitudOtrosAnimalesEsterWrap', tieneOtrosAnimales);

            const tuvoMascotasAntes = document.querySelector('input[name="tuvoMascotasAntes"]:checked')?.value === 'si';
            setConditionalVisibility('solicitudMascotasAnterioresWrap', tuvoMascotasAntes);
        }

        // Función para mostrar alertas con Tailwind
        function showAlert(message, type) {
            const alertDiv = document.getElementById('alert');
            alertDiv.textContent = message;
            
            if (type === 'success') {
                alertDiv.className = 'mb-4 rounded-lg p-4 text-sm font-medium whitespace-pre-line bg-green-50 text-green-800 border border-green-200';
            } else {
                alertDiv.className = 'mb-4 rounded-lg p-4 text-sm font-medium whitespace-pre-line bg-red-50 text-red-800 border border-red-200';
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

        function normalizeText(value, fallback = '') {
            const text = String(value ?? '').trim();
            return text === '' ? fallback : text;
        }

        async function loadRequestedAdoptionIds(forceReload = false) {
            if (!CAN_REQUEST_ADOPTION) {
                requestedAdoptionIds = new Set();
                requestedAdoptionIdsLoaded = true;
                return;
            }

            if (!forceReload && requestedAdoptionIdsLoaded) {
                return;
            }

            try {
                const response = await fetch(MY_REQUESTED_ADOPTIONS_URL, {
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                const result = await response.json().catch(() => ({}));
                const ids = response.ok && Array.isArray(result?.data) ? result.data : [];

                requestedAdoptionIds = new Set(
                    ids
                        .map((id) => normalizeText(id, ''))
                        .filter((id) => id !== '')
                );
            } catch (error) {
                console.error('No se pudieron cargar las solicitudes existentes del ciudadano:', error);
                requestedAdoptionIds = new Set();
            } finally {
                requestedAdoptionIdsLoaded = true;
            }
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function escapeAttr(value) {
            return escapeHtml(value);
        }

        function bindAdoptionImageFallbackHandlers(scopeElement) {
            if (!scopeElement) {
                return;
            }

            scopeElement.querySelectorAll('.adoption-card-image').forEach((img) => {
                if (img.dataset.errorBound === '1') {
                    return;
                }

                img.dataset.errorBound = '1';
                img.addEventListener('error', () => {
                    const panel = img.closest('.adoption-image-panel');
                    if (!panel) {
                        return;
                    }

                    panel.className = 'adoption-image-panel flex shrink-0 items-center justify-center overflow-hidden rounded-xl border border-dashed border-teal-200 bg-teal-100/60';
                    panel.style.width = 'clamp(5.5rem, 16vw, 7rem)';
                    panel.style.height = 'clamp(5.5rem, 16vw, 7rem)';
                    panel.innerHTML = '<p class="px-2 text-center text-xs font-medium text-teal-800">Sin foto</p>';
                });
            });
        }

        function openEditAdoptionModal(adopcion) {
            if (!EDIT_ADOPTION_MODAL || !EDIT_ADOPTION_FORM || !EDIT_ADOPTION_ID) {
                return;
            }

            EDIT_ADOPTION_FORM.reset();
            EDIT_ADOPTION_ID.value = adopcion.id || '';
            document.getElementById('editNombreAnimal').value = adopcion.nombre || '';
            document.getElementById('editTipoAnimal').value = adopcion.tipo || '';
            document.getElementById('editSexo').value = adopcion.sexo || '';
            document.getElementById('editEdad').value = adopcion.edad || '';
            document.getElementById('editRaza').value = adopcion.raza || '';
            document.getElementById('editDetalles').value = adopcion.detalles || '';

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

        // Función para cargar adopciones desde la API
        async function loadAdopciones() {
            const adopcionList = document.getElementById('adopcionList');
            
            try {
                if (CAN_REQUEST_ADOPTION && !requestedAdoptionIdsLoaded) {
                    await loadRequestedAdoptionIds();
                }

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
                        adopcionList.innerHTML = '<div class="col-span-full text-center py-12"><p class="text-gray-500 text-lg">No hay adopciones registradas aún</p><p class="text-gray-400 text-sm mt-2">¡Sé el primero en registrar una mascota!</p></div>';
                        return;
                    }

                    adopcionesArray.forEach((adopcion) => {
                        const rawAdoptionId = normalizeText(adopcion.id || adopcion._docId, '');
                        const adoptionId = escapeAttr(rawAdoptionId);
                        const parsedDate = new Date(adopcion.fecha);
                        const fecha = Number.isNaN(parsedDate.getTime()) ? 'fecha no disponible' : parsedDate.toLocaleDateString('es-ES');
                        const ownerId = Number(adopcion.createdBy || 0);
                        const isOwner = CURRENT_USER_ID !== null && Number(CURRENT_USER_ID) === ownerId;
                        const canModifyThisAdoption = CAN_MANAGE_ALL_ADOPTIONS || isOwner;
                        const canEditAction = Boolean(CAN_REGISTER_ADOPTION && canModifyThisAdoption && rawAdoptionId);
                        const hasExistingRequest = CAN_REQUEST_ADOPTION && rawAdoptionId !== '' && requestedAdoptionIds.has(rawAdoptionId);
                        const canRequestAction = Boolean(!CAN_REGISTER_ADOPTION && CAN_REQUEST_ADOPTION && rawAdoptionId && !hasExistingRequest);
                        const hasActionButton = canEditAction || canRequestAction;
                        const nombreAnimalRaw = normalizeText(adopcion.nombreAnimal, 'Mascota sin nombre');
                        const tipoAnimalRaw = normalizeText(adopcion.tipoAnimal, 'Tipo no especificado');
                        const sexoRaw = String(adopcion.sexo ?? '').trim().toLowerCase();
                        const sexoValue = sexoRaw === 'hembra' || sexoRaw === 'macho' ? sexoRaw : '';
                        const sexoLabel = sexoValue === 'hembra' ? 'Hembra' : (sexoValue === 'macho' ? 'Macho' : 'No especificado');
                        const razaRaw = normalizeText(adopcion.raza, 'No especificada');
                        const detallesRaw = normalizeText(adopcion.detalles, '');
                        const imageUrlRaw = normalizeText(adopcion.imageUrl, '');
                        const edadNumero = Number(adopcion.edad);
                        const edad = Number.isFinite(edadNumero) && edadNumero >= 0 ? edadNumero : null;
                        const edadTexto = edad === null ? 'N/D' : String(edad);
                        const edadUnidad = edad === null ? '' : (edad === 1 ? ' año' : ' años');
                        const nombreAnimal = escapeHtml(nombreAnimalRaw);
                        const tipoAnimal = escapeHtml(tipoAnimalRaw);
                        const sexo = escapeHtml(sexoLabel);
                        const raza = escapeHtml(razaRaw);
                        const detalles = escapeHtml(detallesRaw);
                        const imageUrl = escapeAttr(imageUrlRaw);
                        const imageAlt = escapeAttr(`Foto de ${nombreAnimalRaw}`);
                        const imagePanelSizeStyle = 'width: clamp(5.5rem, 16vw, 7rem); height: clamp(5.5rem, 16vw, 7rem);';
                        const imagePanel = imageUrlRaw
                            ? `<div class="adoption-image-panel shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-slate-100" style="${imagePanelSizeStyle}">
                                    <img src="${imageUrl}" alt="${imageAlt}" class="preview-image adoption-card-image block h-full w-full cursor-zoom-in transition duration-300 group-hover:scale-[1.01]" style="display:block;width:100%;height:100%;object-fit:contain;object-position:center;padding:0.25rem;" data-full-image="${imageUrl}" data-image-alt="${imageAlt}" loading="lazy">
                                </div>`
                            : `<div class="adoption-image-panel flex shrink-0 items-center justify-center rounded-xl border border-dashed border-teal-200 bg-teal-100/60" style="${imagePanelSizeStyle}">
                                    <p class="px-2 text-center text-xs font-medium text-teal-800">Sin foto</p>
                                </div>`;

                        const html = `
                            <div class="group flex h-full flex-col rounded-2xl border border-teal-100 bg-teal-50 p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                                <div class="mb-3">
                                    <h3 class="text-xl font-extrabold leading-tight text-gray-900">${nombreAnimal}</h3>
                                    <p class="mt-1 text-sm font-medium text-gray-600">${tipoAnimal}</p>
                                </div>
                                <div class="mb-3 flex flex-1 items-start gap-4">
                                    <div class="min-w-0 flex-1">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="rounded-lg border border-teal-100 bg-white px-3 py-2">
                                                <span class="text-sm text-gray-700"><strong>Edad:</strong> ${edadTexto}${edadUnidad}</span>
                                            </div>
                                            <div class="rounded-lg border border-teal-100 bg-white px-3 py-2">
                                                <span class="text-sm text-gray-700"><strong>Raza:</strong> ${raza}</span>
                                            </div>
                                            <div class="rounded-lg border border-teal-100 bg-white px-3 py-2 col-span-2 sm:col-span-1">
                                                <span class="text-sm text-gray-700"><strong>Sexo:</strong> ${sexo}</span>
                                            </div>
                                        </div>
                                        ${detalles ? `<div class="mt-3 rounded-lg border border-teal-100 bg-white p-3">
                                            <p class="text-sm text-gray-600"><strong class="text-gray-900">Detalles:</strong> ${detalles}</p>
                                        </div>` : ''}
                                    </div>
                                    ${imagePanel}
                                </div>
                                <div class="mt-auto flex items-center gap-2 border-t border-teal-200 pt-3 ${hasActionButton ? 'justify-between' : 'justify-start'}">
                                    <span class="text-xs text-gray-600">Registrado el ${fecha}</span>
                                    ${canEditAction
                                        ? `<button
                                            type="button"
                                            class="edit-adoption inline-flex items-center justify-center rounded-full bg-teal-700 px-4 py-2 text-sm font-extrabold text-white shadow-md shadow-teal-700/30 ring-2 ring-teal-300 transition hover:bg-teal-800 hover:shadow-lg focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-teal-300"
                                            data-id="${adoptionId}"
                                            data-nombre="${escapeAttr(normalizeText(adopcion.nombreAnimal, ''))}"
                                            data-tipo="${escapeAttr(normalizeText(adopcion.tipoAnimal, ''))}"
                                            data-sexo="${escapeAttr(sexoValue)}"
                                            data-edad="${edad === null ? '' : edad}"
                                            data-raza="${escapeAttr(normalizeText(adopcion.raza, ''))}"
                                            data-detalles="${escapeAttr(normalizeText(adopcion.detalles, ''))}"
                                        >
                                            Editar mascota registrada
                                        </button>`
                                        : canRequestAction
                                        ? `<button
                                            type="button"
                                            class="request-adoption inline-flex items-center justify-center rounded-full bg-teal-700 px-4 py-2 text-sm font-extrabold text-white shadow-md shadow-teal-700/30 ring-2 ring-teal-300 transition hover:bg-teal-800 hover:shadow-lg focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-teal-300 ${rawAdoptionId ? '' : 'opacity-50 cursor-not-allowed'}"
                                            data-id="${adoptionId}"
                                            data-name="${escapeAttr(normalizeText(adopcion.nombreAnimal, ''))}"
                                            title="Abrir formulario de solicitud"
                                            ${rawAdoptionId ? '' : 'disabled'}
                                        >
                                            Solicitar adopción
                                        </button>`
                                        : ''}
                                </div>
                            </div>
                        `;
                        adopcionList.innerHTML += html;
                    });

                    bindAdoptionImageFallbackHandlers(adopcionList);
                } else {
                    adopcionList.innerHTML = '<div class="col-span-full text-center py-12"><p class="text-gray-500 text-lg">No hay adopciones registradas aún</p><p class="text-gray-400 text-sm mt-2">¡Sé el primero en registrar una mascota!</p></div>';
                }
            } catch (error) {
                console.error('Error al cargar adopciones:', error);
                adopcionList.innerHTML = '<div class="col-span-full text-center py-12"><p class="text-red-600 text-lg font-medium">Error al cargar adopciones</p><p class="text-gray-400 text-sm mt-2">Intenta recargar la página</p></div>';
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

        if (CLOSE_ADOPTION_REQUEST_MODAL) {
            CLOSE_ADOPTION_REQUEST_MODAL.addEventListener('click', closeAdoptionRequestModal);
        }

        if (CANCEL_ADOPTION_REQUEST_MODAL) {
            CANCEL_ADOPTION_REQUEST_MODAL.addEventListener('click', closeAdoptionRequestModal);
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

        if (ADOPTION_REQUEST_MODAL) {
            ADOPTION_REQUEST_MODAL.addEventListener('click', function (event) {
                if (event.target === ADOPTION_REQUEST_MODAL) {
                    closeAdoptionRequestModal();
                }
            });
        }

        if (CLOSE_ADOPTION_REQUEST_SUCCESS_MODAL) {
            CLOSE_ADOPTION_REQUEST_SUCCESS_MODAL.addEventListener('click', closeAdoptionRequestSuccessModal);
        }

        if (CONFIRM_ADOPTION_REQUEST_SUCCESS_MODAL) {
            CONFIRM_ADOPTION_REQUEST_SUCCESS_MODAL.addEventListener('click', closeAdoptionRequestSuccessModal);
        }

        if (ADOPTION_REQUEST_SUCCESS_MODAL) {
            ADOPTION_REQUEST_SUCCESS_MODAL.addEventListener('click', function (event) {
                if (event.target === ADOPTION_REQUEST_SUCCESS_MODAL) {
                    closeAdoptionRequestSuccessModal();
                }
            });
        }

        document.querySelectorAll('input[name="hogarIntegrantes[]"], input[name="tieneOtrosAnimales"], input[name="tuvoMascotasAntes"]').forEach((element) => {
            element.addEventListener('change', updateAdoptionRequestConditionalFields);
        });

        if (ADOPTION_REQUEST_CONFIRMATION) {
            ADOPTION_REQUEST_CONFIRMATION.addEventListener('change', updateAdoptionRequestSubmitState);
        }

        updateAdoptionRequestSubmitState();

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeImagePreview();
                closeAdoptionRequestModal();
                closeAdoptionRequestSuccessModal();
                closeEditAdoptionModal();
            }
        });

        // Eliminar adopción registrada
        document.getElementById('adopcionList').addEventListener('click', async function (event) {
            const previewImage = event.target.closest('.preview-image');
            if (previewImage) {
                openImagePreview(previewImage.dataset.fullImage || previewImage.src, previewImage.dataset.imageAlt || previewImage.alt || 'Vista previa');
                return;
            }

            const requestBtn = event.target.closest('.request-adoption');
            if (requestBtn && !requestBtn.disabled) {
                if (!IS_AUTHENTICATED) {
                    showAlert('Debes iniciar sesión para solicitar una adopción', 'error');
                    return;
                }

                if (!CAN_REQUEST_ADOPTION) {
                    showAlert('Solo usuarios con rol ciudadano pueden enviar solicitudes', 'error');
                    return;
                }

                const adoptionId = requestBtn.dataset.id;
                if (!adoptionId) {
                    showAlert('No se pudo identificar la mascota', 'error');
                    return;
                }

                if (requestedAdoptionIds.has(String(adoptionId).trim())) {
                    showAlert('Ya enviaste una solicitud para esta mascota.', 'error');
                    return;
                }

                openAdoptionRequestModal(adoptionId, requestBtn.dataset.name || 'esta mascota');
                return;
            }

            const editBtn = event.target.closest('.edit-adoption');
            if (editBtn && !editBtn.disabled) {
                if (!IS_AUTHENTICATED) {
                    showAlert('Debes iniciar sesión para editar esta adopción', 'error');
                    return;
                }

                if (!CAN_REGISTER_ADOPTION) {
                    showAlert('No tienes permisos para editar adopciones', 'error');
                    return;
                }

                openEditAdoptionModal({
                    id: editBtn.dataset.id,
                    nombre: editBtn.dataset.nombre,
                    tipo: editBtn.dataset.tipo,
                    sexo: editBtn.dataset.sexo,
                    edad: editBtn.dataset.edad,
                    raza: editBtn.dataset.raza,
                    detalles: editBtn.dataset.detalles,
                });
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

        if (EDIT_ADOPTION_FORM) {
            EDIT_ADOPTION_FORM.addEventListener('submit', async function (event) {
                event.preventDefault();

                if (!IS_AUTHENTICATED) {
                    showAlert('Debes iniciar sesión para editar esta adopción', 'error');
                    return;
                }

                if (!CAN_REGISTER_ADOPTION) {
                    showAlert('No tienes permisos para editar adopciones', 'error');
                    return;
                }

                const adoptionId = EDIT_ADOPTION_ID?.value || '';
                const nombreAnimal = document.getElementById('editNombreAnimal')?.value?.trim() || '';
                const tipoAnimal = document.getElementById('editTipoAnimal')?.value || '';
                const sexo = document.getElementById('editSexo')?.value || '';
                const edad = document.getElementById('editEdad')?.value || '';
                const raza = document.getElementById('editRaza')?.value?.trim() || '';
                const detalles = document.getElementById('editDetalles')?.value?.trim() || '';
                const fotoMascota = document.getElementById('editFotoMascota')?.files?.[0];

                if (!adoptionId) {
                    showAlert('No se pudo identificar la adopción a editar', 'error');
                    return;
                }

                if (!nombreAnimal || !tipoAnimal || !sexo || !edad || !raza) {
                    showAlert('Completa todos los campos obligatorios', 'error');
                    return;
                }

                if (Number(edad) < 0 || Number(edad) > 50) {
                    showAlert('Por favor, ingresa una edad válida (0-50)', 'error');
                    return;
                }

                const originalText = EDIT_ADOPTION_SUBMIT_BTN?.textContent || 'Guardar cambios';
                if (EDIT_ADOPTION_SUBMIT_BTN) {
                    EDIT_ADOPTION_SUBMIT_BTN.disabled = true;
                    EDIT_ADOPTION_SUBMIT_BTN.textContent = 'Guardando...';
                }

                try {
                    const formData = new FormData();
                    formData.append('_method', 'PATCH');
                    formData.append('nombreAnimal', nombreAnimal);
                    formData.append('tipoAnimal', tipoAnimal);
                    formData.append('sexo', sexo);
                    formData.append('edad', edad);
                    formData.append('raza', raza);
                    formData.append('detalles', detalles);

                    if (fotoMascota) {
                        formData.append('fotoMascota', fotoMascota);
                    }

                    const updateUrl = UPDATE_ADOPTION_URL_TEMPLATE.replace('__ID__', encodeURIComponent(adoptionId));
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
                        showAlert('Error: ' + (result.message || 'No se pudo actualizar la mascota'), 'error');
                        return;
                    }

                    closeEditAdoptionModal();
                    showAlert('¡Mascota actualizada con éxito!', 'success');
                    loadAdopciones();
                } catch (error) {
                    showAlert('Error: ' + error.message, 'error');
                } finally {
                    if (EDIT_ADOPTION_SUBMIT_BTN) {
                        EDIT_ADOPTION_SUBMIT_BTN.disabled = false;
                        EDIT_ADOPTION_SUBMIT_BTN.textContent = originalText;
                    }
                }
            });
        }

        if (ADOPTION_REQUEST_FORM) {
            ADOPTION_REQUEST_FORM.addEventListener('submit', async function (event) {
                event.preventDefault();

                if (!IS_AUTHENTICATED) {
                    showAlert('Debes iniciar sesión para solicitar una adopción', 'error');
                    return;
                }

                if (!CAN_REQUEST_ADOPTION) {
                    showAlert('Solo usuarios con rol ciudadano pueden enviar solicitudes', 'error');
                    return;
                }

                const adoptionId = ADOPTION_REQUEST_ADOPTION_ID?.value;
                const normalizedAdoptionId = String(adoptionId || '').trim();
                const nombreCompleto = document.getElementById('solicitudNombreCompleto')?.value?.trim();
                const direccionCiudad = document.getElementById('solicitudDireccionCiudad')?.value?.trim();
                const tipoVivienda = document.getElementById('solicitudTipoVivienda')?.value;
                const experienciaMascotas = document.getElementById('solicitudExperienciaMascotas')?.value?.trim();
                const patioJardin = document.querySelector('input[name="patioJardin"]:checked')?.value || '';
                const hogarIntegrantes = Array.from(document.querySelectorAll('input[name="hogarIntegrantes[]"]:checked')).map((element) => element.value);
                const hogarIntegrantesOtros = document.getElementById('solicitudHogarOtros')?.value?.trim();
                const tieneOtrosAnimales = document.querySelector('input[name="tieneOtrosAnimales"]:checked')?.value || '';
                const tiposOtrosAnimales = document.getElementById('solicitudTiposOtrosAnimales')?.value?.trim();
                const otrosAnimalesEsterilizados = document.querySelector('input[name="otrosAnimalesEsterilizados"]:checked')?.value || '';
                const tuvoMascotasAntes = document.querySelector('input[name="tuvoMascotasAntes"]:checked')?.value || '';
                const detalleMascotasAnteriores = document.getElementById('solicitudDetalleMascotasAnteriores')?.value?.trim();
                const dispuestoAtencionVeterinaria = document.querySelector('input[name="dispuestoAtencionVeterinaria"]:checked')?.value || '';
                const telefono = document.getElementById('solicitudTelefono')?.value?.trim();
                const mensaje = document.getElementById('solicitudMensaje')?.value?.trim();

                if (!adoptionId) {
                    showAlert('No se pudo identificar la mascota', 'error');
                    return;
                }

                if (normalizedAdoptionId !== '' && requestedAdoptionIds.has(normalizedAdoptionId)) {
                    showAlert('Ya enviaste una solicitud para esta mascota.', 'error');
                    return;
                }

                if (!nombreCompleto || !direccionCiudad || !tipoVivienda || !experienciaMascotas || !patioJardin || !tieneOtrosAnimales || !tuvoMascotasAntes || !dispuestoAtencionVeterinaria || !telefono || !mensaje) {
                    showAlert('Completa todos los campos obligatorios del formulario', 'error');
                    return;
                }

                if (hogarIntegrantes.length === 0) {
                    showAlert('Selecciona al menos una opción en "¿Quiénes viven en tu hogar?"', 'error');
                    return;
                }

                if (hogarIntegrantes.includes('otros') && !hogarIntegrantesOtros) {
                    showAlert('Debes especificar la opción "Otros" del hogar', 'error');
                    return;
                }

                if (tieneOtrosAnimales === 'si' && (!tiposOtrosAnimales || !otrosAnimalesEsterilizados)) {
                    showAlert('Completa la información de tus otros animales', 'error');
                    return;
                }

                if (tuvoMascotasAntes === 'si' && !detalleMascotasAnteriores) {
                    showAlert('Describe qué mascotas has tenido y por cuánto tiempo', 'error');
                    return;
                }

                const submitBtn = ADOPTION_REQUEST_SUBMIT_BTN || ADOPTION_REQUEST_FORM.querySelector('button[type="submit"]');
                const originalText = submitBtn?.textContent || 'Enviar solicitud';

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Enviando...';
                }

                try {
                    const formData = new FormData();
                    formData.append('nombreCompleto', nombreCompleto);
                    formData.append('direccionCiudad', direccionCiudad);
                    formData.append('tipoVivienda', tipoVivienda);
                    formData.append('experienciaMascotas', experienciaMascotas);
                    formData.append('patioJardin', patioJardin);
                    hogarIntegrantes.forEach((item) => formData.append('hogarIntegrantes[]', item));
                    if (hogarIntegrantesOtros) {
                        formData.append('hogarIntegrantesOtros', hogarIntegrantesOtros);
                    }
                    formData.append('tieneOtrosAnimales', tieneOtrosAnimales);
                    if (tiposOtrosAnimales) {
                        formData.append('tiposOtrosAnimales', tiposOtrosAnimales);
                    }
                    if (otrosAnimalesEsterilizados) {
                        formData.append('otrosAnimalesEsterilizados', otrosAnimalesEsterilizados);
                    }
                    formData.append('tuvoMascotasAntes', tuvoMascotasAntes);
                    if (detalleMascotasAnteriores) {
                        formData.append('detalleMascotasAnteriores', detalleMascotasAnteriores);
                    }
                    formData.append('dispuestoAtencionVeterinaria', dispuestoAtencionVeterinaria);
                    formData.append('telefono', telefono);
                    formData.append('mensaje', mensaje);

                    const requestUrl = REQUEST_URL_TEMPLATE.replace('__ID__', encodeURIComponent(adoptionId));
                    const response = await fetch(requestUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN || '',
                        },
                        body: formData,
                    });

                    const result = await response.json().catch(() => ({}));

                    if (!response.ok || !result.success) {
                        if (response.status === 409) {
                            await loadRequestedAdoptionIds(true);
                            loadAdopciones();
                        }

                        showAlert('Error: ' + (result.message || 'No se pudo enviar la solicitud'), 'error');
                        return;
                    }

                    if (normalizedAdoptionId !== '') {
                        requestedAdoptionIds.add(normalizedAdoptionId);
                        requestedAdoptionIdsLoaded = true;
                    }

                    closeAdoptionRequestModal();
                    openAdoptionRequestSuccessModal();
                    loadAdopciones();
                } catch (error) {
                    showAlert('Error: ' + error.message, 'error');
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }

                    updateAdoptionRequestSubmitState();
                }
            });
        }

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
            const sexo = document.getElementById('sexo').value;
            const edad = document.getElementById('edad').value;
            const raza = document.getElementById('raza').value.trim();
            const detalles = document.getElementById('detalles').value.trim();
            const fotoMascota = document.getElementById('fotoMascota').files[0];

            // Validaciones básicas
            if (!nombreAnimal || !raza || !edad || !tipoAnimal || !sexo) {
                showAlert('Por favor, completa todos los campos obligatorios', 'error');
                return;
            }

            if (edad < 0 || edad > 50) {
                showAlert('Por favor, ingresa una edad válida (0-50)', 'error');
                return;
            }

            // Desactivar botón mientras se envía
            const btn = adopcionForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Registrando...';

            try {
                const formData = new FormData();
                formData.append('nombreAnimal', nombreAnimal);
                formData.append('tipoAnimal', tipoAnimal);
                formData.append('sexo', sexo);
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
                    body: formData,
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
                btn.textContent = 'Registrar adopción';
            }
            });
        }
    </script>
</x-app-layout>
