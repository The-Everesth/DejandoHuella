<?php

namespace App\Http\Requests\Auth;

use App\Services\Firestore\FirestoreLoginProvisioningService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');
        $remember = $this->boolean('remember');

        // Validación previa: usuario desactivado
        $firestoreService = app(\App\Services\Firestore\UsersFirestoreService::class);
        $user = $firestoreService->getUserByEmail($this->input('email'));
        if ($user && (isset($user['status']) && $user['status'] !== 'active')) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'Tu cuenta ha sido suspendida temporalmente. Si crees que se trata de un error, contacta al administrador.',
            ]);
        }

        if (! Auth::attempt($credentials, $remember)) {
            try {
                app(FirestoreLoginProvisioningService::class)
                    ->provisionMissingLocalUser((string) $this->input('email'), (string) $this->input('password'));
            } catch (\Throwable $e) {
                Log::warning('Firestore login provisioning failed while handling login attempt', [
                    'email' => (string) $this->input('email'),
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
