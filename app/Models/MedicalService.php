<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalService extends Model
{
    protected $fillable = ['name','type','created_by','is_active'];

    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_medical_service')
            ->withTimestamps();
    }
}

