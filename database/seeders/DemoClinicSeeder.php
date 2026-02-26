<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\MedicalService;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoClinicSeeder extends Seeder
{
    public function run(): void
    {
        $serviceA = MedicalService::firstOrCreate(
            ['name' => 'Consulta general'],
            ['type' => 'system', 'is_active' => true]
        );

        $serviceB = MedicalService::firstOrCreate(
            ['name' => 'Vacunación'],
            ['type' => 'system', 'is_active' => true]
        );

        $vet = User::firstOrCreate(
            ['email' => 'vet.demo@dejandohuella.local'],
            ['name' => 'Vet Demo', 'password' => bcrypt('password')]
        );

        if (method_exists($vet, 'assignRole') && ! $vet->hasRole('veterinario')) {
            $vet->assignRole('veterinario');
        }

        $existingClinic = Clinic::where('user_id', $vet->id)->first();

        if (! $existingClinic) {
            DB::table('clinics')->insert([
                'user_id' => $vet->id,
                'owner_vet_id' => $vet->id,
                'name' => 'Clínica Demo Central',
                'phone' => '6180000000',
                'email' => 'clinic.demo@dejandohuella.local',
                'address' => 'Zona Centro, Durango',
                'address_line' => 'Zona Centro, Durango',
                'description' => 'Clínica de demostración',
                'opening_hours' => 'Lun-Vie 9:00-18:00',
                'website' => null,
                'is_public' => true,
                'city' => 'Durango',
                'state' => 'Durango',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('clinics')
                ->where('id', $existingClinic->id)
                ->update([
                    'owner_vet_id' => DB::raw('COALESCE(owner_vet_id, user_id)'),
                    'is_public' => true,
                    'updated_at' => now(),
                ]);
        }

        $clinic = Clinic::where('user_id', $vet->id)->firstOrFail();

        $clinic->services()->syncWithoutDetaching([
            $serviceA->id => [
                'price' => 250,
                'currency' => 'MXN',
                'duration_minutes' => 30,
                'is_available' => true,
            ],
            $serviceB->id => [
                'price' => 300,
                'currency' => 'MXN',
                'duration_minutes' => 35,
                'is_available' => true,
            ],
        ]);
    }
}
