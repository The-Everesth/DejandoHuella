<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\AdoptionsController;
use App\Http\Controllers\ClinicsController;

use App\Http\Controllers\AdoptionPublicController;
use App\Http\Controllers\MyAdoptionController;
use App\Http\Controllers\AdoptionRequestController;

use App\Http\Controllers\Public\ServiceBrowserController;
use App\Http\Controllers\AppointmentController;

use App\Http\Controllers\SupportTicketController;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\MedicalServiceController;
use App\Http\Controllers\Admin\SupportTicketAdminController;

// Ruta para el formulario de adopciones
Route::get('/adopciones-form', [AdoptionsController::class, 'form'])->name('adopciones.form');

// Clinics public pages
Route::get('/servicios-medicos', [ClinicsController::class, 'catalog'])->name('clinics.catalog');
Route::get('/servicios-medicos/{id}', [ClinicsController::class, 'showView'])->name('clinics.show');

// Veterinarian panel (single clinic per vet)
Route::get('/veterinario/clinica', [ClinicsController::class, 'vetForm'])->name('clinics.vet.form');
Route::post('/veterinario/clinica', [ClinicsController::class, 'store'])->name('clinics.vet.store');
Route::delete('/veterinario/clinica', [ClinicsController::class, 'destroy'])->name('clinics.vet.destroy');
use App\Http\Controllers\Vet\ClinicController;
use App\Http\Controllers\Vet\ClinicServiceController;

// rutas de prueba Firestore
use App\Services\Firestore\ClinicsFirestoreService;
use App\Services\Firestore\AppointmentsFirestoreService;
use App\Services\Firestore\UsersFirestoreService;
use Illuminate\Support\Str;

//HOME PÚBLICO
Route::view('/', 'home')->name('home');


// --- rutas de prueba para sincronización de usuarios -------------------------------------------
Route::get('/test/firestore/sync-user', function (UsersFirestoreService $svc) {
    if (! auth()->check()) {
        return response()->json(['error' => 'Requiere autenticación'], 401);
    }
    $result = $svc->syncFromUser(auth()->user());
    return response()->json($result);
});

Route::get('/test/firestore/list-users', function (UsersFirestoreService $svc) {
    return response()->json($svc->list());
});

// --- fin de rutas de prueba ------------------------------------------------------------------

// TEST: Firestore clinic save debug
Route::get('/test/firestore/save-clinic', function () {
    if (! auth()->check()) {
        return response()->json(['error' => 'Require auth'], 401);
    }
    if (! auth()->user()->hasAnyRole(['veterinario', 'admin'])) {
        return response()->json(['error' => 'Require vet role'], 403);
    }
    
    $clinicsService = app(\App\Services\Firestore\ClinicsFirestoreService::class);
    $testData = [
        'name' => 'Clínica Test ' . now()->timestamp,
        'address' => 'Test Address',
        'phone' => '555-1234',
        'services' => ['svc1', 'svc2'],
        'published' => false,
    ];
    
    try {
        \Log::info('TEST: Creating clinic with data:', $testData);
        $result = $clinicsService->createOrUpdateClinicForVet(auth()->id(), $testData);
        \Log::info('TEST: Clinic created, result:', $result);
        return response()->json(['success' => true, 'data' => $result]);
    } catch (\Throwable $e) {
        \Log::error('TEST: Clinic creation failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return response()->json(['success' => false, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
    }
});

// TEST: Dual-write sync test
Route::get('/test/firestore/dual-write', function () {
    if (! auth()->check()) {
        return response()->json(['error' => 'Require auth'], 401);
    }
    if (! auth()->user()->hasAnyRole(['veterinario', 'admin'])) {
        return response()->json(['error' => 'Require vet role'], 403);
    }
    
    $clinicsService = app(\App\Services\Firestore\ClinicsFirestoreService::class);
    
    // Create clinic in MySQL
    $clinicData = [
        'name' => 'Test Clinic ' . now()->timestamp,
        'address' => 'Test Address ' . now()->timestamp,
        'phone' => '555-' . random_int(1000, 9999),
        'email' => 'test-' . now()->timestamp . '@test.local',
        'user_id' => auth()->id(),
        'is_public' => true,
    ];
    
    try {
        $clinic = \App\Models\Clinic::create($clinicData);
        \Log::info('TEST dual-write: Clinic created in MySQL', ['id' => $clinic->id]);
        
        // Sync to Firestore
        $fsResult = $clinicsService->syncFromModel($clinic);
        \Log::info('TEST dual-write: Clinic synced to Firestore', ['fsId' => 'c_' . $clinic->id]);
        
        // Verify
        $fsClinic = $clinicsService->getClinicById('c_' . $clinic->id);
        \Log::info('TEST dual-write: Verified in Firestore', ['found' => !!$fsClinic]);
        
        return response()->json([
            'success' => true,
            'mysql_clinic' => $clinic->toArray(),
            'firestore_clinic' => $fsClinic,
            'firestore_id' => 'c_' . $clinic->id,
        ]);
    } catch (\Throwable $e) {
        \Log::error('TEST dual-write: Failed', ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
});

// TEST: Update sync test - Diagnose update/patch issues
Route::get('/test/firestore/update-sync/{clinic}', function (\App\Models\Clinic $clinic) {
    if (! auth()->check()) {
        return response()->json(['error' => 'Require auth'], 401);
    }
    if ($clinic->user_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    $clinicsService = app(\App\Services\Firestore\ClinicsFirestoreService::class);
    
    try {
        \Log::info('TEST update-sync: Starting update test', ['clinicId' => $clinic->id]);
        
        // Update clinic in MySQL
        $newData = [
            'name' => 'Updated: ' . $clinic->name . ' ' . now()->timestamp,
            'phone' => '777-' . random_int(1000, 9999),
            'description' => 'TEST UPDATE - ' . now()->toDateTimeString(),
        ];
        
        $clinic->update($newData);
        $clinic->refresh();
        
        \Log::info('TEST update-sync: Clinic updated in MySQL', [
            'clinicId' => $clinic->id,
            'newName' => $clinic->name,
            'newPhone' => $clinic->phone,
        ]);
        
        // Sync to Firestore
        $fsResult = $clinicsService->syncFromModel($clinic);
        \Log::info('TEST update-sync: Sync completed', ['clinicId' => $clinic->id]);
        
        // Verify in Firestore
        $fsClinic = $clinicsService->getClinicById('c_' . $clinic->id);
        \Log::info('TEST update-sync: Firestore verification', [
            'found' => !!$fsClinic,
            'fsName' => $fsClinic['name'] ?? 'NULL',
            'syncedName' => $clinic->name,
            'match' => ($fsClinic['name'] ?? '') === $clinic->name,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Update test completed - check laravel.log for details',
            'mysql_clinic' => [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'phone' => $clinic->phone,
                'description' => $clinic->description,
            ],
            'firestore_clinic' => $fsClinic ? [
                'id' => $fsClinic['id'] ?? null,
                'name' => $fsClinic['name'] ?? null,
                'phone' => $fsClinic['phone'] ?? null,
                'description' => $fsClinic['description'] ?? null,
            ] : null,
            'matched' => $fsClinic && ($fsClinic['name'] ?? '') === $clinic->name,
        ]);
    } catch (\Throwable $e) {
        \Log::error('TEST update-sync: Failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

//ADOPCIONES PÚBLICAS (solo ver)
Route::get('/adoptions', [AdoptionPublicController::class, 'index'])->name('adoptions.index');
Route::get('/adoptions/{post}', [AdoptionPublicController::class, 'show'])->name('adoptions.show');

// SERVICIOS PÚBLICOS (explorar)
Route::get('/services', [ServiceBrowserController::class, 'index'])->name('services.index');
Route::get('/services/{service}/clinics', [ServiceBrowserController::class, 'clinics'])->name('services.clinics');

// AUTH + VERIFIED (todo lo interno)
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Mascotas (dueños)
    Route::resource('pets', PetController::class);

    // Mis publicaciones adopción (requiere login)
    Route::get('/my/adoptions', [MyAdoptionController::class, 'index'])->name('myadoptions.index');
    Route::get('/my/adoptions/create', [MyAdoptionController::class, 'create'])->name('myadoptions.create');
    Route::post('/my/adoptions', [MyAdoptionController::class, 'store'])->name('myadoptions.store');
    Route::patch('/my/adoptions/{post}/toggle', [MyAdoptionController::class, 'toggle'])->name('myadoptions.toggle');
    Route::get('/my/adoptions/{post}/requests', [MyAdoptionController::class, 'requests'])->name('myadoptions.requests');

    // Solicitudes adopción (requiere login)
    Route::post('/adoptions/{post}/request', [AdoptionRequestController::class, 'store'])->name('adoptions.request.store');
    Route::get('/my/requests', [AdoptionRequestController::class, 'myRequests'])->name('myrequests.index');
    Route::patch('/requests/{adoptionRequest}/status', [AdoptionRequestController::class, 'setStatus'])->name('requests.status');

    // Citas (ciudadano)
    Route::get('/appointments/create/{clinic}/{service}', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/my/appointments', [AppointmentController::class, 'myAppointments'])->name('my.appointments');

    // Tickets (usuario)
    Route::get('/tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [SupportTicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [SupportTicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [SupportTicketController::class, 'show'])->name('tickets.show');

    // ADMIN (un solo bloque)
    Route::middleware(['role:admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            // Usuarios/roles
            Route::get('/users', [UserRoleController::class, 'index'])->name('users.index');
            Route::get('/users/pending', [UserRoleController::class, 'pending'])->name('users.pending');
            Route::get('/users/{user}/role', [UserRoleController::class, 'edit'])->name('users.role.edit');
            Route::put('/users/{user}/role', [UserRoleController::class, 'update'])->name('users.role.update');
            Route::put('/users/{user}/approve', [UserRoleController::class, 'approve'])->name('users.approve');
            Route::put('/users/{user}/reject', [UserRoleController::class, 'reject'])->name('users.reject');
            Route::get('/users/{user}/edit', [UserRoleController::class, 'editUser'])->name('users.edit');
            Route::put('/users/{user}', [UserRoleController::class, 'updateUser'])->name('users.update');
            Route::delete('/users/{user}', [UserRoleController::class, 'destroy'])->name('users.destroy');
            Route::post('/users/{user}/restore', [UserRoleController::class, 'restore'])->name('users.restore');



            // Servicios médicos CRUD
            Route::resource('services', MedicalServiceController::class)->parameters(['services' => 'service']);

            // Tickets admin
            Route::get('/tickets', [SupportTicketAdminController::class, 'index'])->name('tickets.index');
            Route::get('/tickets/{ticket}', [SupportTicketAdminController::class, 'show'])->name('tickets.show');
            Route::post('/tickets/{ticket}/reply', [SupportTicketAdminController::class, 'reply'])->name('tickets.reply');
            Route::post('/tickets/{ticket}/close', [SupportTicketAdminController::class, 'close'])->name('tickets.close');
        });

    //Veterinario
    Route::middleware(['role:veterinario'])
        ->prefix('vet')
        ->name('vet.')
        ->group(function () {
            Route::resource('clinics', ClinicController::class);
            Route::get('clinics/{clinic}/services', [ClinicServiceController::class, 'edit'])->name('clinics.services.edit');
            Route::post('clinics/{clinic}/services', [ClinicServiceController::class, 'update'])->name('clinics.services.update');

            Route::get('appointments', [AppointmentController::class, 'vetAppointments'])->name('appointments.index');
            Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'setStatus'])->name('appointments.status');

            Route::delete('/users/{user}', [UserRoleController::class, 'destroy'])->name('users.destroy');

        });
});

require __DIR__.'/auth.php';
