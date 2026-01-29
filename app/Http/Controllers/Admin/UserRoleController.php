<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;




class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $role = $request->query('role');       // admin|veterinario|refugio|ciudadano
        $status = $request->query('status');   // pending|approved|rejected|all
        $trashed = $request->query('trashed'); // null|with|only

        $users = User::query()
            // SoftDeletes: mostrar también desactivados si se pide
            ->when($trashed === 'with', fn ($query) => $query->withTrashed())
            ->when($trashed === 'only', fn ($query) => $query->onlyTrashed())

            // búsqueda por texto
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })

            // filtro por rol (Spatie)
            ->when($role, function ($query) use ($role) {
                $query->whereHas('roles', function ($r) use ($role) {
                    $r->where('name', $role);
                });
            })

            // filtro por estado de solicitud (si lo manejas)
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $query->where('role_request_status', $status);
            })

            ->with('roles')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q', 'role', 'status', 'trashed'));
    }



    public function edit(User $user)
    {
        $roles = Role::query()->orderBy('name')->get();
        $currentRole = $user->roles->first()?->name; // asumimos 1 rol principal

        return view('admin.users.edit-role', compact('user', 'roles', 'currentRole'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        // Evitar que el admin se quite a sí mismo el rol admin (opcional pero recomendado)
        if (auth()->id() === $user->id && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'No puedes quitarte el rol admin a ti mismo.']);
        }

        // Para el proyecto, manejamos 1 rol principal: syncRoles reemplaza el/los rol(es)
        $user->syncRoles([$data['role']]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Rol actualizado.');
    }

    public function pending()
{
    $users = User::query()
        ->where('role_request_status', 'pending')
        ->orderByDesc('role_requested_at')
        ->get();

    return view('admin.users.pending', compact('users'));
}

public function approve(User $user)
{
    if ($user->role_request_status !== 'pending' || !in_array($user->requested_role, ['veterinario', 'refugio'], true)) {
        return back()->withErrors(['role' => 'No hay solicitud válida para aprobar.']);
    }

    $user->syncRoles([$user->requested_role]);
    $user->role_request_status = 'approved';
    $user->role_reviewed_at = now();
    $user->save();

    return back()->with('success', 'Solicitud aprobada.');
}

public function reject(User $user)
{
    if ($user->role_request_status !== 'pending') {
        return back()->withErrors(['role' => 'No hay solicitud válida para rechazar.']);
    }

    // Se queda como ciudadano
    $user->syncRoles(['ciudadano']);
    $user->role_request_status = 'rejected';
    $user->role_reviewed_at = now();
    $user->save();

    return back()->with('success', 'Solicitud rechazada (se mantiene como ciudadano).');
}
public function editUser(User $user)
{
    return view('admin.users.edit', compact('user'));
}

public function updateUser(Request $request, User $user)
{
    $data = $request->validate([
        'name' => ['required','string','max:255'],
        'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
    ]);

    $user->update($data);

    return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado.');
}

public function destroy(User $user)
{
    if (auth()->id() === $user->id) {
        return back()->withErrors(['user' => 'No puedes eliminar tu propio usuario.']);
    }

    $user->delete();

    return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado.');
}
public function restore(string $user)
{
    $u = User::withTrashed()->findOrFail($user);

    $u->restore();

    return redirect()->route('admin.users.index')->with('success', 'Usuario restaurado.');
}


}
