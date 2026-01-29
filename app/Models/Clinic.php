<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $fillable = [
        'user_id','name','phone','email','address','description','opening_hours','website','is_public'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }


    public function services()
    {
        return $this->belongsToMany(MedicalService::class, 'clinic_medical_service')
            ->withPivot(['price', 'currency', 'duration_minutes', 'is_available'])
            ->withTimestamps();
    }

}
