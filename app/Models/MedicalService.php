<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class MedicalService extends Model
{
    protected $fillable = ['name','type','created_by','is_active'];

    public function clinics()
    {
        $pivotTable = Schema::hasTable('clinic_medical_service')
            ? 'clinic_medical_service'
            : 'clinic_services';

        return $this->belongsToMany(Clinic::class, $pivotTable)
            ->withTimestamps();
    }
}

