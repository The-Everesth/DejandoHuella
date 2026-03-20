@php
    $profileHasErrors = $errors->has('name') || $errors->has('email') || $errors->has('profile_photo') || $errors->has('profile');
@endphp

<section
    x-data="profileForm({ initialName: @js(old('name', $user->name)), initialEmail: @js(old('email', $user->email)), initialPhotoUrl: @js($user->profile_photo_url) })"
    class="space-y-6"
>
    <header class="space-y-2">
        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-teal-700">Perfil</p>
        <h2 class="text-2xl font-semibold text-slate-900">Información personal</h2>

        <p class="max-w-2xl text-sm leading-6 text-slate-600">
            Tus datos actuales se cargan automáticamente para que solo modifiques lo que quieras actualizar.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    @if ($profileHasErrors)
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
            No pudimos guardar el perfil. Revisa los campos marcados e inténtalo de nuevo.
        </div>
    @endif

    <form
        x-ref="profileUpdateForm"
        method="post"
        action="{{ route('profile.update') }}"
        enctype="multipart/form-data"
        class="space-y-6"
        @submit.prevent="touch('name'); touch('email'); if (!hasBlockingErrors && !submitting) showConfirmation = true"
    >
        @csrf
        @method('patch')

        <div class="rounded-3xl border border-slate-200 bg-slate-50/80 p-5">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div class="shrink-0">
                    <img
                        x-show="photoPreview"
                        :src="photoPreview || ''"
                        alt="Vista previa de la foto de perfil"
                        class="h-24 w-24 rounded-full object-cover shadow-lg ring-4 ring-white"
                    >

                    <div
                        x-show="!photoPreview"
                        class="flex h-24 w-24 items-center justify-center rounded-full bg-teal-700 text-2xl font-semibold text-white shadow-lg"
                    >
                        {{ $user->profile_initials }}
                    </div>
                </div>

                <div class="flex-1 space-y-3">
                    <div class="space-y-1">
                        <x-input-label for="profile_photo" value="Foto de perfil" />
                        <p id="profile-photo-help" class="text-sm text-slate-500">
                            Sube una imagen clara para personalizar tu perfil. Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo: 2 MB.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <label
                            for="profile_photo"
                            class="inline-flex cursor-pointer items-center rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-100"
                        >
                            Seleccionar imagen
                        </label>

                        <p x-show="photoName" x-text="photoName" class="text-sm text-slate-600" role="status" aria-live="polite"></p>
                    </div>

                    <input
                        id="profile_photo"
                        name="profile_photo"
                        type="file"
                        class="sr-only"
                        accept="image/png,image/jpeg,image/webp"
                        aria-describedby="profile-photo-help"
                        x-on:change="previewPhoto($event)"
                    >

                    <p x-show="photoError" x-text="photoError" class="text-sm text-rose-600" role="status" aria-live="polite"></p>
                    <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
                </div>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div class="space-y-2">
                <x-input-label for="name" value="Nombre del perfil" />
                <p id="name_help" class="text-sm text-slate-500">
                    Usa tu nombre real o el nombre con el que quieres identificarte en la plataforma.
                </p>

                <x-text-input
                    id="name"
                    name="name"
                    type="text"
                    class="block w-full border-slate-300"
                    value="{{ old('name', $user->name) }}"
                    required
                    autofocus
                    autocomplete="name"
                    pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñÜü&quot;' -]+$"
                    title="Usa solo letras, espacios, comillas dobles, apóstrofes o guiones."
                    aria-describedby="name_help name_feedback"
                    x-model.trim="name"
                    x-on:input="touch('name')"
                    x-on:blur="touch('name')"
                    x-bind:aria-invalid="nameError ? 'true' : 'false'"
                />

                <p id="name_feedback" x-show="nameError" x-text="nameError" class="text-sm text-rose-600" role="status" aria-live="polite"></p>
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div class="space-y-2">
                <x-input-label for="email" value="Correo electrónico" />
                <p id="email_help" class="text-sm text-slate-500">
                    Ingresa un correo válido para recibir avisos importantes y recuperar tu cuenta si lo necesitas.
                </p>

                <x-text-input
                    id="email"
                    name="email"
                    type="email"
                    class="block w-full border-slate-300"
                    value="{{ old('email', $user->email) }}"
                    required
                    autocomplete="username"
                    aria-describedby="email_help email_feedback"
                    x-model.trim="email"
                    x-on:input="touch('email')"
                    x-on:blur="touch('email')"
                    x-bind:aria-invalid="emailError ? 'true' : 'false'"
                />

                <p id="email_feedback" x-show="emailError" x-text="emailError" class="text-sm text-rose-600" role="status" aria-live="polite"></p>
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                        <p class="text-sm text-amber-800">
                            Tu correo electrónico aún no está verificado.

                            <button form="send-verification" class="font-semibold underline decoration-amber-400 underline-offset-4 hover:text-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 rounded-md">
                                Reenviar correo de verificación
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-emerald-700" role="status" aria-live="polite">
                                Se envió un nuevo enlace de verificación a tu correo electrónico.
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 pt-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <x-primary-button x-bind:disabled="submitting || hasBlockingErrors" x-bind:class="(submitting || hasBlockingErrors) ? 'opacity-70 cursor-not-allowed' : ''">
                    <span x-text="submitting ? 'Guardando...' : 'Guardar cambios'"></span>
                </x-primary-button>

                <p class="text-sm text-slate-500">
                    El formulario mantiene tu nombre y tu correo actuales para que solo cambies lo necesario.
                </p>
            </div>

            @if (session('status') === 'profile-updated')
                <p class="text-sm font-medium text-emerald-700" role="status" aria-live="polite">
                    Perfil actualizado con éxito.
                </p>
            @endif
        </div>
    </form>

    {{-- Modal de confirmación: guardar cambios del perfil --}}
    <div
        x-show="showConfirmation"
        class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
        style="display: none;"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-profile-title"
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
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 id="confirm-profile-title" class="text-base font-semibold text-slate-900">¿Guardar cambios del perfil?</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            Se actualizará tu información personal con los datos del formulario. Podrás volver a editarlos cuando quieras.
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
                        @click="showConfirmation = false; submitting = true; $refs.profileUpdateForm.submit()"
                    >
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
