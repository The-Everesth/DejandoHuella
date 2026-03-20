@php
    $passwordHasErrors = count($errors->updatePassword->all()) > 0;
@endphp

<section x-data="passwordSecurityForm({ minLength: 8 })" class="space-y-6">
    <header class="space-y-2">
        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-teal-700">Seguridad</p>
        <h2 class="text-2xl font-semibold text-slate-900">Actualizar contraseña</h2>

        <p class="max-w-2xl text-sm leading-6 text-slate-600">
            Para confirmar este cambio sensible debes ingresar tu contraseña actual y elegir una clave más robusta.
        </p>
    </header>

    @if ($passwordHasErrors)
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
            No pudimos actualizar tu contraseña. Revisa los requisitos de seguridad e inténtalo de nuevo.
        </div>
    @endif

    <form x-ref="passwordUpdateForm" method="post" action="{{ route('password.update') }}" class="space-y-6" @submit.prevent="touch('password'); touch('confirmation'); if (!passwordError && !confirmationError && !submitting) showConfirmation = true">
        @csrf
        @method('put')

        <div class="space-y-2">
            <x-input-label for="update_password_current_password" value="Contraseña actual" />
            <p id="current_password_help" class="text-sm text-slate-500">
                Esta confirmación adicional evita que terceros cambien tu contraseña sin autorización.
            </p>

            <x-text-input
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="block w-full border-slate-300"
                autocomplete="current-password"
                required
                aria-describedby="current_password_help"
                x-model="currentPassword"
            />

            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div class="rounded-3xl border border-slate-200 bg-slate-50/80 p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-900">Fuerza de la contraseña</p>
                    <p class="mt-1 text-sm text-slate-500">
                        Usa al menos 8 caracteres con mayúsculas, minúsculas, números y símbolos.
                    </p>
                </div>

                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="strengthBadgeClass" x-text="strengthLabel"></span>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-2" aria-hidden="true">
                <div class="h-2 rounded-full transition" :class="strengthSegmentClass(1)"></div>
                <div class="h-2 rounded-full transition" :class="strengthSegmentClass(2)"></div>
                <div class="h-2 rounded-full transition" :class="strengthSegmentClass(3)"></div>
            </div>

            <p class="mt-4 text-sm" :class="feedbackToneClass" x-text="feedbackMessage" role="status" aria-live="polite"></p>

            <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                <li class="flex items-center gap-2 text-sm" :class="ruleTextClass(rules.length)">
                    <span class="h-2.5 w-2.5 rounded-full" :class="ruleDotClass(rules.length)"></span>
                    Mínimo 8 caracteres
                </li>
                <li class="flex items-center gap-2 text-sm" :class="ruleTextClass(rules.upper)">
                    <span class="h-2.5 w-2.5 rounded-full" :class="ruleDotClass(rules.upper)"></span>
                    Al menos una mayúscula
                </li>
                <li class="flex items-center gap-2 text-sm" :class="ruleTextClass(rules.lower)">
                    <span class="h-2.5 w-2.5 rounded-full" :class="ruleDotClass(rules.lower)"></span>
                    Al menos una minúscula
                </li>
                <li class="flex items-center gap-2 text-sm" :class="ruleTextClass(rules.number)">
                    <span class="h-2.5 w-2.5 rounded-full" :class="ruleDotClass(rules.number)"></span>
                    Al menos un número
                </li>
                <li class="flex items-center gap-2 text-sm sm:col-span-2" :class="ruleTextClass(rules.symbol)">
                    <span class="h-2.5 w-2.5 rounded-full" :class="ruleDotClass(rules.symbol)"></span>
                    Al menos un símbolo especial
                </li>
            </ul>
        </div>

        <div class="space-y-2">
            <x-input-label for="update_password_password" value="Nueva contraseña" />
            <p id="password_help" class="text-sm text-slate-500">
                El indicador cambia en tiempo real para ayudarte a elegir una contraseña segura.
            </p>

            <x-text-input
                id="update_password_password"
                name="password"
                type="password"
                class="block w-full border-slate-300"
                autocomplete="new-password"
                required
                minlength="8"
                aria-describedby="password_help password_feedback"
                x-model="password"
                x-on:input="touch('password')"
                x-on:blur="touch('password')"
                x-bind:aria-invalid="passwordError ? 'true' : 'false'"
            />

            <p id="password_feedback" x-show="passwordError" x-text="passwordError" class="text-sm text-rose-600" role="status" aria-live="polite"></p>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div class="space-y-2">
            <x-input-label for="update_password_password_confirmation" value="Confirmar nueva contraseña" />
            <p id="password_confirmation_help" class="text-sm text-slate-500">
                Repite la contraseña nueva para confirmar que no haya errores al escribirla.
            </p>

            <x-text-input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="block w-full border-slate-300"
                autocomplete="new-password"
                required
                aria-describedby="password_confirmation_help password_confirmation_feedback"
                x-model="confirmation"
                x-on:input="touch('confirmation')"
                x-on:blur="touch('confirmation')"
                x-bind:aria-invalid="confirmationError ? 'true' : 'false'"
            />

            <p id="password_confirmation_feedback" x-show="confirmationError" x-text="confirmationError" class="text-sm text-rose-600" role="status" aria-live="polite"></p>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 pt-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <x-primary-button x-bind:disabled="submitting || !!passwordError || !!confirmationError" x-bind:class="(submitting || !!passwordError || !!confirmationError) ? 'opacity-70 cursor-not-allowed' : ''">
                    <span x-text="submitting ? 'Actualizando...' : 'Guardar contraseña'"></span>
                </x-primary-button>

                <p class="text-sm text-slate-500">
                    Por seguridad, esta acción requiere tu contraseña actual antes de guardar.
                </p>
            </div>

            @if (session('status') === 'password-updated')
                <p class="text-sm font-medium text-emerald-700" role="status" aria-live="polite">
                    Contraseña actualizada con éxito.
                </p>
            @endif
        </div>
    </form>

    {{-- Modal de confirmación: cambio de contraseña --}}
    <div
        x-show="showConfirmation"
        class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
        style="display: none;"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-password-title"
    >
        <div
            x-show="showConfirmation"
            class="fixed inset-0 transition-all"
            @click="showConfirmation = false"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        </div>

        <div class="flex min-h-full items-center justify-center">
            <div
                x-show="showConfirmation"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-md rounded-[1.5rem] bg-white p-6 shadow-2xl"
                @keydown.escape.window="showConfirmation = false"
            >
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 id="confirm-password-title" class="text-base font-semibold text-slate-900">¿Confirmar cambio de contraseña?</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            Tu contraseña se actualizará de inmediato. Necesitarás la nueva clave en tu próxima sesión.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-full px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition"
                        @click="showConfirmation = false"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="rounded-full bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition"
                        @click="showConfirmation = false; submitting = true; $refs.passwordUpdateForm.submit()"
                    >
                        Confirmar cambio
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
