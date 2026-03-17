<x-app-layout>
    <x-page-title
        title="Gestión de adopciones"
        subtitle="Modera las publicaciones visibles de refugios y veterinarias."
    />

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <x-card class="mb-4">
        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.adoptions.index', array_merge(request()->except('page'), ['visibility' => null])) }}">
                    <x-button variant="{{ empty($visibility) ? 'soft' : 'outline' }}">Activas</x-button>
                </a>

                <a href="{{ route('admin.adoptions.index', array_merge(request()->except('page'), ['visibility' => 'hidden'])) }}">
                    <x-button variant="{{ $visibility === 'hidden' ? 'soft' : 'outline' }}">Ocultas</x-button>
                </a>

                <a href="{{ route('admin.adoptions.index', array_merge(request()->except('page'), ['visibility' => 'with'])) }}">
                    <x-button variant="{{ $visibility === 'with' ? 'soft' : 'outline' }}">Todas</x-button>
                </a>
            </div>

            <div class="flex flex-wrap gap-2 text-sm font-semibold">
                <span class="rounded-full bg-teal-50 px-3 py-1 text-teal-700">Visibles: {{ $summary['active'] ?? 0 }}</span>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">Ocultas: {{ $summary['hidden'] ?? 0 }}</span>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-amber-700">Total: {{ $summary['all'] ?? 0 }}</span>
            </div>

            <form method="GET" class="grid grid-cols-1 gap-3 items-end md:grid-cols-5">
                <input type="hidden" name="visibility" value="{{ $visibility }}">

                <div class="md:col-span-2">
                    <label class="font-bold text-gray-800">Buscar</label>
                    <input
                        name="q"
                        value="{{ $q }}"
                        class="mt-2 w-full rounded-xl border p-3"
                        placeholder="Mascota, publicador o correo..."
                    >
                </div>

                <div>
                    <label class="font-bold text-gray-800">Tipo</label>
                    <select name="type" class="mt-2 w-full rounded-xl border p-3">
                        <option value="">Todos</option>
                        <option value="perro" @selected($type === 'perro')>Perro</option>
                        <option value="gato" @selected($type === 'gato')>Gato</option>
                        <option value="conejo" @selected($type === 'conejo')>Conejo</option>
                        <option value="ave" @selected($type === 'ave')>Ave</option>
                        <option value="otro" @selected($type === 'otro')>Otro</option>
                    </select>
                </div>

                <div>
                    <label class="font-bold text-gray-800">Publicador</label>
                    <select name="publisher_role" class="mt-2 w-full rounded-xl border p-3">
                        <option value="">Todos</option>
                        @foreach(($publisherRoleOptions ?? collect()) as $roleOption)
                            <option value="{{ $roleOption }}" @selected($publisherRole === $roleOption)>
                                {{ $publisherRoleLabels[$roleOption] ?? ucfirst($roleOption) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <x-button type="submit" variant="soft" class="w-full">Filtrar</x-button>

                    <a href="{{ route('admin.adoptions.index', ['visibility' => $visibility]) }}" class="w-full">
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
                        <th class="p-4 font-bold">Mascota</th>
                        <th class="p-4 font-bold">Publicado por</th>
                        <th class="p-4 font-bold">Estado</th>
                        <th class="p-4 font-bold">Publicado</th>
                        <th class="p-4 font-bold text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($adoptions as $item)
                        <tr class="hover:bg-slate-50 {{ $item['is_hidden'] ? 'opacity-80' : '' }}">
                            <td class="p-4">
                                <div class="font-extrabold text-slate-900">{{ $item['pet_name'] }}</div>
                                <div class="text-slate-600">{{ $item['pet_type'] }} | {{ $item['sex_label'] }}</div>
                                <div class="text-xs text-slate-500">Raza: {{ $item['breed'] }}</div>
                            </td>

                            <td class="p-4">
                                <div class="font-bold text-slate-900">{{ $item['publisher_name'] }}</div>
                                <div class="text-slate-600">{{ $item['publisher_email'] }}</div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-teal-100 px-3 py-1 font-bold text-teal-900">
                                        {{ $item['publisher_role_label'] ?? $item['publisher_role'] }}
                                    </span>

                                    @if($item['publisher_deleted'])
                                        <span class="rounded-full bg-amber-100 px-3 py-1 font-bold text-amber-900">
                                            Cuenta desactivada
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="p-4">
                                <div class="flex flex-col gap-2">
                                    <span class="w-fit rounded-full px-3 py-1 font-bold {{ $item['is_hidden'] ? 'bg-slate-200 text-slate-700' : 'bg-green-100 text-green-900' }}">
                                        {{ $item['is_hidden'] ? 'Oculta' : 'Visible' }}
                                    </span>

                                    @if($item['is_hidden'])
                                        <span class="text-xs text-slate-500">Oculta: {{ $item['hidden_at'] }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="p-4 text-slate-600">
                                {{ $item['created_at'] }}
                            </td>

                            <td class="p-4">
                                <div class="flex justify-end gap-2">
                                    <form
                                        class="toggle-adoption-visibility-form"
                                        method="POST"
                                        action="{{ route('admin.adoptions.visibility.update', array_merge(['adoptionId' => $item['id']], request()->except('page'))) }}"
                                        data-pet-name="{{ $item['pet_name'] }}"
                                        data-next-hidden="{{ $item['is_hidden'] ? 0 : 1 }}"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_hidden" value="{{ $item['is_hidden'] ? 0 : 1 }}">
                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center rounded-full px-4 py-2 font-bold transition {{ $item['is_hidden'] ? 'bg-gray-900 text-white hover:opacity-90' : 'bg-red-600 text-white hover:bg-red-700' }}"
                                        >
                                            {{ $item['is_hidden'] ? 'Mostrar' : 'Ocultar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="p-6 text-center text-slate-600" colspan="5">
                                No hay publicaciones con esos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $adoptions->links() }}
        </div>
    </x-card>

    <div id="hideAdoptionConfirmModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/70 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="text-lg font-bold text-gray-900">Confirmar ocultamiento</h3>
            <p id="hideAdoptionConfirmMessage" class="mt-2 text-sm text-gray-600">
                Vas a ocultar esta publicación de adopción.
            </p>

            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button
                    type="button"
                    id="hideAdoptionConfirmClose"
                    class="rounded-full border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    id="hideAdoptionConfirmAccept"
                    class="rounded-full border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                >
                    Sí, ocultar
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('hideAdoptionConfirmModal');
            const message = document.getElementById('hideAdoptionConfirmMessage');
            const closeBtn = document.getElementById('hideAdoptionConfirmClose');
            const acceptBtn = document.getElementById('hideAdoptionConfirmAccept');
            const forms = Array.from(document.querySelectorAll('.toggle-adoption-visibility-form'));
            let pendingForm = null;

            if (!modal || !message || !closeBtn || !acceptBtn || forms.length === 0) {
                return;
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
                pendingForm = null;
            }

            function openModal(form) {
                pendingForm = form;
                const petName = String(form.dataset.petName || 'esta mascota').trim();
                message.textContent = `Vas a ocultar la publicación de ${petName}. Dejará de mostrarse en adopciones para ciudadanos.`;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            forms.forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const nextHiddenValue = String(form.dataset.nextHidden || '').trim();
                    if (nextHiddenValue !== '1') {
                        return;
                    }

                    event.preventDefault();
                    openModal(form);
                });
            });

            closeBtn.addEventListener('click', closeModal);

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            acceptBtn.addEventListener('click', () => {
                if (!pendingForm) {
                    return;
                }

                const formToSubmit = pendingForm;
                closeModal();
                formToSubmit.submit();
            });
        })();
    </script>
</x-app-layout>