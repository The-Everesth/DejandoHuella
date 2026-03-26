<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
// use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Services\Firestore\FirestoreUserRoleService;
use App\Services\Firestore\FirestoreUserRegistrationService;

class RegisteredUserController extends Controller
{
    protected $firestoreRoles;
    protected $firestoreRegistration;

    public function __construct(FirestoreUserRoleService $firestoreRoles, FirestoreUserRegistrationService $firestoreRegistration)
    {
        $this->firestoreRoles = $firestoreRoles;
        $this->firestoreRegistration = $firestoreRegistration;
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:ciudadano,veterinario,refugio'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);


        $selectedRole = $request->input('role');
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $selectedRole,
        ];
        $firestoreUser = $this->firestoreRegistration->create($userData);
        $user = new \App\Models\FirestoreAuthenticatableUser($firestoreUser);

        event(new Registered($user));
        Auth::login($user);
        return redirect(RouteServiceProvider::HOME);
    }
}
