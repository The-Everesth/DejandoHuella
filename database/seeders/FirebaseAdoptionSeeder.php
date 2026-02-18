<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\FirebaseService;

class FirebaseAdoptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firebase = app(FirebaseService::class);

        $data = [
            'nombre' => 'Firulais',
            'edad' => '3 años',
            'especie' => 'Perro',
            'raza' => 'Mestizo',
            'fecha' => now()->toDateTimeString(),
            'contacto' => 'prueba@ejemplo.com'
        ];

        $result = $firebase->saveAdoption($data);

        $this->command->info('Firebase push: ' . json_encode($result));
    }
}
