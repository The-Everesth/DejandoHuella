<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Services\Firestore\FirestoreUserRoleService;

class RegisteredUserController extends Controller
{
    protected $firestoreRoles;

    public function __construct(FirestoreUserRoleService $firestoreRoles)
    {
        $this->firestoreRoles = $firestoreRoles;
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $selectedRole = $request->input('role');
        $user->assignRole('ciudadano'); // rol por defecto

        // Si pidió veterinario/refugio, se autoaprueba
        if (in_array($selectedRole, ['veterinario', 'refugio'], true)) {
            $user->syncRoles([$selectedRole]);
            $user->requested_role = null;
            $user->role_request_status = 'approved';
            $user->role_requested_at = now();
            $user->role_reviewed_at = now();
            $user->save();

            $this->firestoreRoles->resolveRoleRequest($user, 'approved', $selectedRole);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
