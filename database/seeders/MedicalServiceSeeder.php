<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicalService;

class MedicalServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            'Consulta general',
            'Vacunación',
            'Desparasitación',
            'Esterilización',
            'Cirugía',
            'Rayos X',
            'Ultrasonido',
            'Laboratorio / análisis',
            'Hospitalización',
            'Urgencias',
            'Baño y estética',
        ];

        foreach ($services as $name) {
            MedicalService::firstOrCreate(
                ['name' => $name],
                ['type' => 'system', 'is_active' => true]
            );
        }
    }
}

