<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\Firestore\AdoptionsFirestoreService;

class FirebaseAdoptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firebase = app(AdoptionsFirestoreService::class);

        $data = [
            'nombre' => 'Firulais',
            'edad' => '3 años',
            'especie' => 'Perro',
            'raza' => 'Mestizo',
            'fecha' => now()->toDateTimeString(),
            'contacto' => 'prueba@ejemplo.com'
        ];

        $result = $firebase->create($data, uniqid('adop_'));

        $this->command->info('Firebase push: ' . json_encode($result));
    }
}
