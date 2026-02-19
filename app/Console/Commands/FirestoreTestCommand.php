<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Firestore\ClinicsFirestoreService;
use App\Services\Firestore\AppointmentsFirestoreService;
use Illuminate\Support\Str;

class FirestoreTestCommand extends Command
{
    protected $signature = 'firestore:test';
    protected $description = 'Realiza operaciones rápidas en Firestore para comprobar servicios';

    public function handle(ClinicsFirestoreService $clinics, AppointmentsFirestoreService $appointments)
    {
        $this->info('Creando clinic de prueba');
        $clinicId = 'cli-' . Str::random(6);
        $clinicData = [
            'name' => 'Test clinic ' . Str::random(3),
            'mysqlUserId' => null,
            'created_at' => now()->toIso8601String(),
        ];
        $doc = $clinics->create($clinicData, $clinicId);
        $this->info('Clinic creada: ' . json_encode($doc));

        $this->info('Listando clinics');
        $list = $clinics->list();
        $this->info(json_encode(array_keys($list)));

        $this->info('Creando appointment de prueba');
        $apptId = 'app-' . Str::random(6);
        $apptData = [
            'clinicId' => $clinicId,
            'service' => 'dry-cleaning',
            'mysqlUserId' => null,
            'scheduled_at' => now()->addDay()->toIso8601String(),
        ];
        $appt = $appointments->create($apptData, $apptId);
        $this->info('Appointment creado: ' . json_encode($appt));

        $this->info('Verificando que el appointment existe');
        $check = $appointments->get($apptId);
        if ($check) {
            $this->info('Appointment encontrado: ' . json_encode($check));
        } else {
            $this->error('Appointment no encontrado');
        }

        return 0;
    }
}
