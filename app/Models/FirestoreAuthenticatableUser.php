<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class FirestoreAuthenticatableUser implements AuthenticatableContract
{
    use Authenticatable;

    // Duplicated user fields (from User DTO)
    public $id;
    // ...existing code...

    /**
     * Devuelve los nombres de roles del usuario (compatibilidad con User)
     */
    public function getRoleNames()
    {
        return collect(app(\App\Services\Firestore\FirestoreUserRoleService::class)->getRolesByUser($this));
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

    // Add any other fields as needed

    public function __construct($data = [])
    {
        if ($data instanceof User) {
            foreach (get_object_vars($data) as $key => $value) {
                $this->$key = $value;
            }
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Permite acceder a accessors como propiedades ($user->profile_initials)
     */
    public function __get($key)
    {
        $method = 'get' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key))) . 'Attribute';
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }
        if (property_exists($this, $key)) {
            return $this->$key;
        }
        return null;
    }

    /**
     * Iniciales del usuario para avatar (compatibilidad con User)
     */
    public function getProfileInitialsAttribute(): string
    {
        $name = $this->name ?? '';
        $parts = preg_split('/\s+/', trim($name));
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $initials !== '' ? $initials : 'U';
    }
        /**
     * Verifica si el usuario tiene uno o varios roles (compatibilidad con User)
     */
    public function hasRole($roles): bool
    {
        return app(\App\Services\Firestore\FirestoreUserRoleService::class)->hasRoleByUser($this, $roles);
    }

    public function hasAnyRole(...$roles): bool
    {
        return $this->hasRole($roles);
    }

}