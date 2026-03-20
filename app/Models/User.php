<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Services\Firestore\FirestoreUserRoleService;
use App\Models\Pet;
use App\Models\AdoptionPost;
use App\Models\AdoptionRequest;

use App\Models\Clinic;
use App\Models\VeterinarianProfile;
use App\Models\Appointment;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    public function pets()
    {
        return $this->hasMany(Pet::class, 'owner_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'profile_photo_path',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function adoptionPosts()
    {
        return $this->hasMany(AdoptionPost::class, 'created_by');
    }

    public function adoptionRequests()
    {
        return $this->hasMany(AdoptionRequest::class, 'applicant_id');
    }


    public function veterinarianProfile()
    {
        return $this->hasOne(VeterinarianProfile::class);
    }

    public function clinics()
    {
        return $this->hasMany(Clinic::class, 'user_id');
    }

    public function appointmentsAsOwner()
    {
        return $this->hasMany(Appointment::class, 'owner_id');
    }

    public function appointmentsAsVet()
    {
        return $this->hasMany(Appointment::class, 'vet_id');
    }

    public function supportTickets()
    {
        return $this->hasMany(\App\Models\SupportTicket::class);
    }

    public function hasRole($roles, $guard = null): bool
    {
        return app(FirestoreUserRoleService::class)->hasRoleByLaravelUserId((int) $this->id, $roles);
    }

    public function hasAnyRole(...$roles): bool
    {
        return $this->hasRole($roles);
    }

    public function getRoleNames()
    {
        return collect(app(FirestoreUserRoleService::class)->getRolesByLaravelUserId((int) $this->id));
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
        if (! $this->profile_photo_path) {
            return null;
        }

        return asset($this->profile_photo_path);
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
