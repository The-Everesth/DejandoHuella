<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Firestore\FirestoreUserRoleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\LengthAwarePaginator;




class UserRoleController extends Controller
{
    protected $firestoreRoles;

    public function __construct(FirestoreUserRoleService $firestoreRoles)
    {
        $this->firestoreRoles = $firestoreRoles;
    }

    public function index(Request $request)
    {
        // $user = auth()->user();
        // $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames() : ($user->role ?? null);
        // dd([
        //     'user' => $user,
        //     'roles' => $roles,
        // ]);

        $q = trim((string) $request->query('q', ''));
        $role = $request->query('role');       // admin|veterinario|refugio|ciudadano
        $status = $request->query('status');   // pending|approved|rejected|all
        $trashed = $request->query('trashed'); // null|with|only

            // Listar usuarios desde Firestore y convertir a instancias de FirestoreAuthenticatableUser
            $allUsers = collect(app(\App\Services\Firestore\UsersFirestoreService::class)->list())
                ->map(fn($u) => $u instanceof \App\Models\FirestoreAuthenticatableUser ? $u : new \App\Models\FirestoreAuthenticatableUser($u));
            // Filtros manuales
            if ($trashed === 'only') {
                $allUsers = $allUsers->whereNotNull('deleted_at')->values();
            } elseif ($trashed !== 'with') {
                $allUsers = $allUsers->whereNull('deleted_at')->values();
            }
            if ($q !== '') {
                $allUsers = $allUsers->filter(function ($user) use ($q) {
                    return (stripos($user->name ?? '', $q) !== false) || (stripos($user->email ?? '', $q) !== false);
                })->values();
            }
            if ($status && $status !== 'all') {
                $allUsers = $allUsers->where('role_request_status', $status)->values();
            }
            $allUsers = $allUsers->sortBy('name')->values();

        if ($role) {
            $allUsers = $allUsers->filter(function ($user) use ($role) {
                return $user->hasRole($role);
            })->values();
        }

        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pageItems = $allUsers->forPage($currentPage, $perPage)->values();

        $users = new LengthAwarePaginator(
            $pageItems,
            $allUsers->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.users.index', compact('users', 'q', 'role', 'status', 'trashed'));
    }



    // Busca usuario en Firestore por docId
    public function edit($userId)
    {
        $userData = app(\App\Services\Firestore\UsersFirestoreService::class)->getUserByDocId($userId);
        if (!$userData) {
            return back()->withErrors(['user' => 'Usuario no encontrado.']);
        }
        $user = new \App\Models\FirestoreAuthenticatableUser($userData);
        $roles = collect([
            (object) ['name' => 'admin'],
            (object) ['name' => 'veterinario'],
            (object) ['name' => 'refugio'],
            (object) ['name' => 'ciudadano'],
        ]);
        $currentRole = $user->getRoleNames()->first();
        return view('admin.users.edit-role', compact('user', 'roles', 'currentRole'));
    }

    public function update(Request $request, $userId)
    {
        $data = $request->validate([
            'role' => 'required|string|in:admin,veterinario,refugio,ciudadano',
        ]);

        $userData = app(\App\Services\Firestore\UsersFirestoreService::class)->getUserByDocId($userId);
        $user = $userData ? new \App\Models\User($userData) : null;
        if (!$user) {
            return back()->withErrors(['user' => 'Usuario no encontrado.']);
        }
        if (auth()->id() === $user->id && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'No puedes quitarte el rol admin a ti mismo.']);
        }
        app(\App\Services\Firestore\UsersFirestoreService::class)->updateUserFields($userId, ['role' => $data['role']]);
        $this->firestoreRoles->resolveRoleRequest($user, 'approved', $data['role']);
        return redirect()->route('admin.users.index')->with('success', 'Rol actualizado.');
    }

    public function pending(UsersFirestoreService $firestore)
    {
        $all = collect($firestore->list());
        $users = $all->filter(function ($user) {
            return ($user['role_request_status'] ?? null) === 'pending';
        })->sortByDesc('role_requested_at')->values();
        return view('admin.users.pending', compact('users'));
    }

public function approve($userId)
{
    $userData = app(\App\Services\Firestore\UsersFirestoreService::class)->getUserByDocId($userId);
    $user = $userData ? new \App\Models\User($userData) : null;
    if (!$user || $user->role_request_status !== 'pending' || !in_array($user->requested_role, ['veterinario', 'refugio'], true)) {
        return back()->withErrors(['role' => 'No hay solicitud válida para aprobar.']);
    }
    app(\App\Services\Firestore\UsersFirestoreService::class)->updateUserFields($userId, [
        'role' => $user->requested_role,
        'role_request_status' => 'approved',
        'role_reviewed_at' => now()->toIso8601String(),
    ]);
    $this->firestoreRoles->resolveRoleRequest($user, 'approved', $user->requested_role);
    return back()->with('success', 'Solicitud aprobada.');
}

public function reject($userId)
{
    $userData = app(\App\Services\Firestore\UsersFirestoreService::class)->getUserByDocId($userId);
    $user = $userData ? new \App\Models\User($userData) : null;
    if (!$user || $user->role_request_status !== 'pending') {
        return back()->withErrors(['role' => 'No hay solicitud válida para rechazar.']);
    }
    app(\App\Services\Firestore\UsersFirestoreService::class)->updateUserFields($userId, [
        'role' => 'ciudadano',
        'role_request_status' => 'rejected',
        'role_reviewed_at' => now()->toIso8601String(),
    ]);
    $this->firestoreRoles->resolveRoleRequest($user, 'rejected');
    return back()->with('success', 'Solicitud rechazada (se mantiene como ciudadano).');
}
    public function editUser($userId)
    {
        $userData = app(\App\Services\Firestore\UsersFirestoreService::class)->getUserByDocId($userId);
        $user = $userData ? new \App\Models\User($userData) : null;
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, $userId)
{
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255'],
        ]);
        app(\App\Services\Firestore\UsersFirestoreService::class)->updateUserFields($userId, $data);
        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado.');
}

    public function destroy($userId)
{
        $userData = app(\App\Services\Firestore\UsersFirestoreService::class)->getUserByDocId($userId);
        $user = $userData ? new \App\Models\User($userData) : null;
        if (auth()->id() === $userId) {
            return back()->withErrors(['user' => 'No puedes eliminar tu propio usuario.']);
        }
        app(\App\Services\Firestore\UsersFirestoreService::class)->updateUserFields($userId, [
            'deleted_at' => now()->toIso8601String(),
            'status' => 'inactive',
        ]);
        return redirect()->route('admin.users.index')->with('success', 'Usuario desactivado.');
}
    public function restore($userId)
    {
        app(\App\Services\Firestore\UsersFirestoreService::class)->updateUserFields($userId, [
            'deleted_at' => null,
            'status' => 'active',
        ]);
        return redirect()->route('admin.users.index')->with('success', 'Usuario restaurado.');
    }


}
