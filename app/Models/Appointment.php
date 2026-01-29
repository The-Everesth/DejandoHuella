<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'clinic_id','medical_service_id','pet_id','owner_id','vet_id',
        'scheduled_at','status','notes'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function clinic() { return $this->belongsTo(Clinic::class); }
    public function service() { return $this->belongsTo(MedicalService::class, 'medical_service_id'); }
    public function pet() { return $this->belongsTo(Pet::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function vet() { return $this->belongsTo(User::class, 'vet_id'); }
}
