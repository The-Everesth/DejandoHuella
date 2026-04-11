<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\AdoptionsController;


use App\Http\Controllers\PublicSite\ServiceBrowserController;
use App\Http\Controllers\AppointmentController;

use App\Http\Controllers\SupportTicketController;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdoptionModerationController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\MedicalServiceController;
use App\Http\Controllers\Admin\SupportTicketAdminController;

use App\Http\Controllers\ClinicsController;

// Ruta para el formulario de adopciones
Route::view('/adopciones-form', 'adopciones')->name('adopciones.form');
Route::post('/adopciones-form', [AdoptionsController::class, 'store'])
    ->middleware(['auth', 'role:veterinario|refugio'])
    ->name('adopciones.store');
Route::delete('/adopciones-form/{id}', [AdoptionsController::class, 'destroy'])
    ->middleware(['auth', 'role:veterinario|refugio'])
    ->name('adopciones.destroy');
Route::post('/adopciones-form/{id}/imagen', [AdoptionsController::class, 'updateImage'])
    ->middleware(['auth', 'role:veterinario|refugio'])
    ->name('adopciones.image.update');
Route::match(['PUT', 'PATCH'], '/adopciones-form/{id}', [AdoptionsController::class, 'update'])
    ->middleware(['auth', 'role:veterinario|refugio'])
    ->name('adopciones.update');
Route::post('/adopciones-form/{id}/solicitud', [AdoptionsController::class, 'storeRequest'])
    ->middleware(['auth', 'role:ciudadano'])
    ->name('adopciones.request.store');
Route::get('/my/requested-adoptions', [AdoptionsController::class, 'myRequestedAdoptionIds'])
    ->middleware(['auth', 'role:ciudadano'])
    ->name('my.requested.adoptions');
use App\Http\Controllers\Vet\ClinicController;
use App\Http\Controllers\Vet\ClinicServiceController;

// rutas de prueba Firestore
use App\Services\Firestore\ClinicsFirestoreService;
use App\Services\Firestore\AppointmentsFirestoreService;
use Illuminate\Support\Str;

//HOME PÚBLICO
Route::view('/', 'home')->name('home');

// --- rutas de prueba para Firestore REST client ------------------------------------------------
Route::get('/test/firestore/create-clinic', function (ClinicsFirestoreService $svc) {
    $id = 'clinic-'.Str::random(6);
    $data = [
        'name' => 'Clinica prueba '.Str::random(3),
        'mysqlUserId' => auth()->id() ?? null,
        'created_at' => now()->toIso8601String(),
    ];
    $doc = $svc->create($data, $id);
    return response()->json(['id' => $id, 'doc' => $doc]);
});

Route::get('/test/firestore/list-clinics', function (ClinicsFirestoreService $svc) {
    return response()->json($svc->list());
});

Route::get('/test/firestore/create-appointment', function (AppointmentsFirestoreService $svc) {
    $id = 'appt-'.Str::random(6);
    $data = [
        'clinicId' => 'some-clinic',
        'service' => 'vaccination',
        'mysqlUserId' => auth()->id() ?? null,
        'scheduled_at' => now()->addDays(1)->toIso8601String(),
    ];
    $doc = $svc->create($data, $id);
    return response()->json(['id' => $id, 'doc' => $doc]);
});

// --- fin de rutas de prueba ------------------------------------------------------------------

// ADOPCIONES (unificado en Firebase)
Route::redirect('/adoptions', '/adopciones-form', 301)->name('adoptions.index');
Route::redirect('/adoptions/{any}', '/adopciones-form', 301)->where('any', '.*')->name('adoptions.show');

// SERVICIOS PÚBLICOS (explorar)
Route::get('/services', [ServiceBrowserController::class, 'index'])->name('services.index');
Route::get('/services/{service}/clinics', [ServiceBrowserController::class, 'clinics'])->name('services.clinics');

// muestra la página pública de una clínica usando Firestore
Route::get('/clinics/{clinic}', [ClinicsController::class, 'publicShow'])
    ->name('clinics.show');

// AUTH + VERIFIED (todo lo interno)
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Mascotas (dueños)
    // Route::resource('pets', PetController::class); // Eliminado para evitar conflicto con rutas my/pets

    Route::post('/adoptions/{post}/request', function () {
        return redirect()->route('adopciones.form');
    })->name('adoptions.request.store');
    Route::get('/my/requests', [AdoptionsController::class, 'myRequests'])
        ->middleware('role:ciudadano')
        ->name('my.requests');
    Route::patch('/my/requests/{requestId}/cancel', [AdoptionsController::class, 'cancelMyRequest'])
        ->middleware('role:ciudadano')
        ->name('my.requests.cancel');
    Route::get('/my/published-requests', [AdoptionsController::class, 'publishedRequests'])
        ->middleware('role:admin|veterinario|refugio')
        ->name('my.published.requests');
    Route::patch('/requests/{requestId}/status', [AdoptionsController::class, 'updateRequestStatus'])
        ->middleware('role:veterinario|refugio')
        ->name('requests.status');
    Route::patch('/requests/{requestId}/note', [AdoptionsController::class, 'updateRequestNote'])
        ->middleware('role:veterinario|refugio')
        ->name('requests.note');

    // Citas (ciudadano)

    Route::get('/appointments/create/{clinic}/{service}', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/my/appointments', [AppointmentController::class, 'myAppointments'])->name('my.appointments');

    // Ver detalle de cita
    Route::get('/my/appointments/{appointment}', [AppointmentController::class, 'show'])->name('my.appointments.show');
    // Cancelar cita
    Route::post('/my/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('my.appointments.cancel');
    // Reagendar cita
    Route::get('/my/appointments/{appointment}/reschedule', [AppointmentController::class, 'rescheduleForm'])->name('my.appointments.reschedule.form');
    Route::post('/my/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('my.appointments.reschedule');

    // Tickets (usuario)
    Route::get('/tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [SupportTicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [SupportTicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [SupportTicketController::class, 'show'])->name('tickets.show');

    // ADMIN (un solo bloque)
    Route::middleware(['web', 'role:admin'])
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

            Route::get('/adoptions', [AdoptionModerationController::class, 'index'])->name('adoptions.index');
            Route::patch('/adoptions/{adoptionId}/visibility', [AdoptionModerationController::class, 'updateVisibility'])
                ->name('adoptions.visibility.update');

            // Servicios médicos CRUD
            Route::resource('services', MedicalServiceController::class)->parameters(['services' => 'service']);

            // Tickets admin
            Route::get('/tickets', [SupportTicketAdminController::class, 'index'])->name('tickets.index');
            // Ruta para admins: ver cualquier ticket por ID
            Route::get('/tickets/{ticket}', [SupportTicketAdminController::class, 'show'])->name('tickets.show');
            Route::post('/tickets/{ticket}/reply', [SupportTicketAdminController::class, 'reply'])->name('tickets.reply');
            Route::post('/tickets/{ticket}/close', [SupportTicketAdminController::class, 'close'])->name('tickets.close');
        });

    Route::get('/vet/my-adoptions', [AdoptionsController::class, 'vetMyAdoptions'])
        ->middleware('role:veterinario|refugio')
        ->name('vet.my.adoptions');

    //Veterinario
    Route::middleware(['role:veterinario'])
        ->prefix('vet')
        ->name('vet.')
        ->group(function () {
            Route::resource('clinics', ClinicController::class);

            // Gestión de servicios médicos por veterinario
            // Servicios globales del veterinario
            Route::prefix('services')->name('services.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Vet\VetServiceController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Vet\VetServiceController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Vet\VetServiceController::class, 'store'])->name('store');
                Route::get('/{service}/edit', [\App\Http\Controllers\Vet\VetServiceController::class, 'edit'])->name('edit');
                Route::put('/{service}', [\App\Http\Controllers\Vet\VetServiceController::class, 'update'])->name('update');
                Route::delete('/{service}', [\App\Http\Controllers\Vet\VetServiceController::class, 'destroy'])->name('destroy');
            });

            // Asignación de servicios a clínicas
            Route::get('clinics/{clinic}/assign-services', [\App\Http\Controllers\Vet\ClinicAssignServiceController::class, 'edit'])->name('clinics.assign_services.edit');
            Route::post('clinics/{clinic}/assign-services', [\App\Http\Controllers\Vet\ClinicAssignServiceController::class, 'update'])->name('clinics.assign_services.update');
            Route::prefix('clinics/{clinic}/services')->name('clinics.services.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Vet\ClinicMedicalServiceController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Vet\ClinicMedicalServiceController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Vet\ClinicMedicalServiceController::class, 'store'])->name('store');
                Route::get('/{service}/edit', [\App\Http\Controllers\Vet\ClinicMedicalServiceController::class, 'edit'])->name('edit');
                Route::put('/{service}', [\App\Http\Controllers\Vet\ClinicMedicalServiceController::class, 'update'])->name('update');
                Route::delete('/{service}', [\App\Http\Controllers\Vet\ClinicMedicalServiceController::class, 'destroy'])->name('destroy');
            });

            Route::get('appointments', [AppointmentController::class, 'vetAppointments'])->name('appointments.index');
            Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'setStatus'])->name('appointments.status');
            Route::patch('appointments/{appointment}/note', [AppointmentController::class, 'saveVetNote'])->name('appointments.note');

            Route::delete('/users/{user}', [UserRoleController::class, 'destroy'])->name('users.destroy');

        });
    
});
// Rutas de mascotas (ciudadanos)
Route::middleware(['role:ciudadano'])->group(function () {
    Route::get('my/pets', [PetController::class, 'index'])->name('my.pets');
    Route::get('my/pets/create', [PetController::class, 'create'])->name('my.pets.create');
    Route::post('my/pets', [PetController::class, 'store'])->name('my.pets.store');
    Route::get('my/pets/{pet}/edit', [PetController::class, 'edit'])->name('my.pets.edit');
    Route::patch('my/pets/{pet}', [PetController::class, 'update'])->name('my.pets.update');
    Route::delete('my/pets/{pet}', [PetController::class, 'destroy'])->name('my.pets.destroy');
});

// Alias legacy para nombres de ruta en notación con puntos
Route::middleware(['auth', 'verified', 'role:ciudadano'])->group(function () {
    Route::redirect('/my/adoptions', '/adopciones-form', 302)->name('my.adoptions');
    Route::redirect('/my/adoptions/create', '/adopciones-form', 302)->name('my.adoptions.create');
    Route::post('/my/adoptions', function () {
        return redirect()->route('adopciones.form');
    })->name('my.adoptions.store');
    Route::patch('/my/adoptions/{post}/toggle', function () {
        return redirect()->route('adopciones.form');
    })->name('my.adoptions.toggle');
    Route::redirect('/my/adoptions/{post}/requests', '/adopciones-form', 302)->name('my.adoptions.requests');
});

require __DIR__.'/auth.php';
