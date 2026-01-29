<x-guest-layout>
    <div class="max-w-md mx-auto">
        <div class="text-center mb-6">
            <div class="text-3xl font-extrabold text-gray-900">Iniciar sesión</div>
            <div class="text-gray-600 mt-1">Accede a tu cuenta de DejandoHuella</div>
        </div>

        <x-card class="rounded-3xl">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="font-bold text-gray-800">Correo</label>
                    <input class="mt-2 w-full border rounded-xl p-3" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <label class="font-bold text-gray-800">Contraseña</label>
                    <input class="mt-2 w-full border rounded-xl p-3" type="password" name="password" required>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center">
                        <input type="checkbox" class="rounded border-gray-300" name="remember">
                        <span class="ms-2 text-sm text-gray-600">Recordarme</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <div class="pt-2 flex gap-2">
                    <x-button type="submit" variant="primary" class="w-full">Entrar</x-button>
                </div>

                <div class="text-center text-sm text-gray-600 mt-4">
                    ¿No tienes cuenta?
                    <a class="underline font-bold" href="{{ route('register') }}">Regístrate</a>
                </div>
            </form>
        </x-card>
    </div>
</x-guest-layout>
