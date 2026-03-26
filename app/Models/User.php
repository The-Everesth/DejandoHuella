<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Services\Firestore\UsersFirestoreService;
use App\Services\Firestore\FirestoreUserRoleService;

/**
 * User DTO/helper for Firestore-only users.
 * No Eloquent, no Authenticatable.
 */
class User
{
    public $id;
    // Permite acceder a accessors como propiedades ($user->profile_initials)
    public function __get($key)
    {
        $method = 'get' . Str::studly($key) . 'Attribute';
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }
        if (property_exists($this, $key)) {
            return $this->$key;
        }
        return null;
    }
    public $name;
    public $email;
    public $profile_photo_url;
    public $status;
    public $role;
    public $created_at;
    public $updated_at;
    public $requested_role;
    public $role_request_status;
    public $role_requested_at;
    public $role_reviewed_at;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }


    // Métodos de roles (delegan a FirestoreUserRoleService, compatible con IDs automáticos y user_code)
    public function hasRole($roles): bool
    {
        return app(FirestoreUserRoleService::class)->hasRoleByUser($this, $roles);
    }
    public function hasAnyRole(...$roles): bool
    {
        return $this->hasRole($roles);
    }
    public function getRoleNames()
    {
        return collect(app(FirestoreUserRoleService::class)->getRolesByUser($this));
    }
    public function assignRole(...$roles)
    {
        $role = $this->firstRoleFromInput($roles);
        if ($role) {
            app(FirestoreUserRoleService::class)->syncPrimaryRole($this, $role);
        }
        return $this;
    }
    public function syncRoles($roles)
    {
        $role = $this->firstRoleFromInput($roles);
        if ($role) {
            app(FirestoreUserRoleService::class)->syncPrimaryRole($this, $role);
        }
        return $this;
    }
    protected function firstRoleFromInput($roles): ?string
    {
        $flattened = [];
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if (is_array($role)) {
                    foreach ($role as $nested) {
                        $flattened[] = $nested;
                    }
                } else {
                    $flattened[] = $role;
                }
            }
        } else {
            $flattened[] = $roles;
        }
        foreach ($flattened as $role) {
            if (is_string($role) && trim($role) !== '') {
                return strtolower(trim($role));
            }
        }
        return null;
    }


    public function getProfilePhotoUrlAttribute(): ?string
    {
        return !empty($this->profile_photo_url) ? $this->profile_photo_url : null;
    }


    public function getProfileInitialsAttribute(): string
    {
        $initials = Str::of($this->name ?? '')
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $segment) => Str::upper(Str::substr($segment, 0, 1)))
            ->implode('');
        return $initials !== '' ? $initials : 'U';
    }

}
