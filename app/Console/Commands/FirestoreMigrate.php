<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Models\MedicalService;
use App\Models\Appointment;
use App\Services\Firestore\ClinicsService;
use App\Services\Firestore\AppointmentsService;
use App\Services\Firestore\PetsService;
use App\Services\Firestore\AdoptionsService;
use App\Services\Firestore\SupportTicketsService;
use App\Services\Firestore\UsersMirrorService;

class FirestoreMigrate extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'firestore:migrate';

    /**
     * The console command description.
     */
    protected $description = 'Copy domain data from MySQL into Firestore (idempotent)';

    public function handle(
        ClinicsService $clinics,
        AppointmentsService $appointments,
        PetsService $pets,
        AdoptionsService $adoptions,
        SupportTicketsService $tickets,
        UsersMirrorService $users
    ) {
        $this->info('Starting Firestore migration (MySQL -> Firestore)');

        // first phase: clinics, medical services, appointments
        $this->migrateClinics($clinics);
        $this->migrateMedicalServices();
        $this->migrateAppointments($appointments);

        // future phases could call other services (pets, adoptions, tickets, users)
        $this->info('Migration finished.');
        return 0;
    }

    protected function migrateClinics(ClinicsService $service)
    {
        $this->info('Migrating clinics...');
        $created = $updated = $failed = 0;

        Clinic::cursor()->each(function (Clinic $clinic) use ($service, &$created, &$updated, &$failed) {
            try {
                $result = $service->syncFromModel($clinic);
                if ($result === 'updated') {
                    $updated++;
                } else {
                    $created++;
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("Clinic {$clinic->id} failed: {$e->getMessage()}");
            }
        });

        $this->info("Clinics: created=$created updated=$updated failed=$failed");
    }

    protected function migrateMedicalServices()
    {
        $this->info('Migrating medical services into Firestore (as collection medicalServices)');
        $created = $updated = $failed = 0;

        $rest = app(\App\Services\Firestore\FirestoreRestClient::class);

        MedicalService::cursor()->each(function (MedicalService $service) use ($rest, &$created, &$updated, &$failed) {
            try {
                $docId = 'ms_'.$service->id;
                $docPath = "medicalServices/{$docId}";
                $exists = $rest->getDocument($docPath);
                $data = [
                    'id' => $service->id,
                    'name' => $service->name,
                    'type' => $service->type,
                    'created_by' => $service->created_by,
                    'is_active' => (bool) $service->is_active,
                    'created_at' => optional($service->created_at)->toDateTimeString(),
                    'updated_at' => optional($service->updated_at)->toDateTimeString(),
                ];

                if ($exists) {
                    $rest->patchDocument($docPath, $data);
                    $updated++;
                } else {
                    $rest->createDocument('medicalServices', $docId, $data);
                    $created++;
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("MedicalService {$service->id} failed: {$e->getMessage()}");
            }
        });

        $this->info("Medical services: created=$created updated=$updated failed=$failed");
    }

    protected function migrateAppointments(AppointmentsService $service)
    {
        $this->info('Migrating appointments...');
        $created = $updated = $failed = 0;

        Appointment::cursor()->each(function (Appointment $appt) use ($service, &$created, &$updated, &$failed) {
            try {
                $result = $service->syncFromModel($appt);
                if ($result === 'updated') {
                    $updated++;
                } else {
                    $created++;
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("Appointment {$appt->id} failed: {$e->getMessage()}");
            }
        });

        $this->info("Appointments: created=$created updated=$updated failed=$failed");
    }
}
