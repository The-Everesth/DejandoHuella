<section class="space-y-6">
    <header class="space-y-2">
        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-rose-600">Zona sensible</p>
        <h2 class="text-2xl font-semibold text-slate-900">
            Eliminar cuenta
        </h2>

        <p class="text-sm leading-6 text-slate-600">
            Esta acción es permanente. Al eliminar tu cuenta también se eliminarán los recursos asociados, incluida tu foto de perfil.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Eliminar cuenta</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                ¿Seguro que quieres eliminar tu cuenta?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Una vez eliminada, tu cuenta no podrá recuperarse. Ingresa tu contraseña actual para confirmar esta acción.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Contraseña actual" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Contraseña actual"
                    autocomplete="current-password"
                    required
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Eliminar cuenta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
