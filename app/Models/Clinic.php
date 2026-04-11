<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Clinic extends Model
{
    protected $fillable = [
        'user_id','name','phone','email','address','description','opening_hours','website','is_public'
    ];

    public function user()
    {
        $foreignKey = Schema::hasColumn($this->getTable(), 'user_id') ? 'user_id' : 'owner_vet_id';

        return $this->belongsTo(\App\Models\User::class, $foreignKey);
    }


    public function services()
    {
        $pivotTable = Schema::hasTable('clinic_medical_service')
            ? 'clinic_medical_service'
            : 'clinic_services';

        $pivotFields = [];
        foreach (['price', 'currency', 'duration_minutes', 'is_available'] as $column) {
            if (Schema::hasColumn($pivotTable, $column)) {
                $pivotFields[] = $column;
            }
        }

        $relation = $this->belongsToMany(MedicalService::class, $pivotTable);
        if (!empty($pivotFields)) {
            $relation->withPivot($pivotFields);
        }

        return $relation->withTimestamps();
    }

    // Relación directa para servicios médicos gestionados por el veterinario
    public function medicalServices()
    {
        return $this->hasMany(MedicalService::class, 'clinic_id');
    }

}
