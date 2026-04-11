<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class MedicalService extends Model
{
    protected $fillable = [
        'name',
        'description',
        'base_price',
        'duration_minutes',
        'clinic_id',
        'vet_id',
        'type',
        'created_by',
        'is_active',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function vet()
    {
        return $this->belongsTo(User::class, 'vet_id');
    }
}

