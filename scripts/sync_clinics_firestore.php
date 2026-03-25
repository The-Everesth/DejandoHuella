<?php
// Archivo: scripts/sync_clinics_firestore.php
// Ejecuta: php scripts/sync_clinics_firestore.php

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

use App\Models\Clinic;
use App\Services\Firestore\ClinicsFirestoreService;

$clinics = Clinic::all();
$firestoreService = app(ClinicsFirestoreService::class);

$updated = 0;
foreach ($clinics as $clinic) {
    $firestoreService->syncFromModel($clinic);
    $updated++;
    echo "Sincronizada clínica ID {$clinic->id}\n";
}
echo "\nTotal de clínicas sincronizadas: $updated\n";
