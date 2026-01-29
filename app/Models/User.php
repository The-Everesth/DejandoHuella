<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Spatie\Permission\Traits\HasRoles;
use App\Models\Pet;
use App\Models\AdoptionPost;
use App\Models\AdoptionRequest;

use App\Models\Clinic;
use App\Models\VeterinarianProfile;
use App\Models\Appointment;

use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;
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

}
