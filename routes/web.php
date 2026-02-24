<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\AdoptionsController;


use App\Http\Controllers\PublicSite\ServiceBrowserController;
use App\Http\Controllers\AppointmentController;

use App\Http\Controllers\SupportTicketController;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\MedicalServiceController;
use App\Http\Controllers\Admin\SupportTicketAdminController;

// Ruta para el formulario de adopciones
Route::view('/adopciones-form', 'adopciones')->name('adopciones.form');
Route::post('/adopciones-form', [AdoptionsController::class, 'store'])->middleware('auth')->name('adopciones.store');
Route::delete('/adopciones-form/{id}', [AdoptionsController::class, 'destroy'])
    ->middleware(['auth', 'role:admin|veterinario|refugio'])
    ->name('adopciones.destroy');
Route::post('/adopciones-form/{id}/imagen', [AdoptionsController::class, 'updateImage'])
    ->middleware(['auth', 'role:admin|veterinario|refugio'])
    ->name('adopciones.image.update');
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

// AUTH + VERIFIED (todo lo interno)
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Mascotas (dueños)
    Route::resource('pets', PetController::class);

    // Adopciones legacy (antes MySQL) -> redirigir al flujo Firebase
    Route::redirect('/my/adoptions', '/adopciones-form', 302)->name('myadoptions.index');
    Route::redirect('/my/adoptions/create', '/adopciones-form', 302)->name('myadoptions.create');
    Route::post('/my/adoptions', function () {
        return redirect()->route('adopciones.form');
    })->name('myadoptions.store');
    Route::patch('/my/adoptions/{post}/toggle', function () {
        return redirect()->route('adopciones.form');
    })->name('myadoptions.toggle');
    Route::redirect('/my/adoptions/{post}/requests', '/adopciones-form', 302)->name('myadoptions.requests');

    Route::post('/adoptions/{post}/request', function () {
        return redirect()->route('adopciones.form');
    })->name('adoptions.request.store');
    Route::redirect('/my/requests', '/adopciones-form', 302)->name('myrequests.index');
    Route::patch('/requests/{adoptionRequest}/status', function () {
        return redirect()->route('adopciones.form');
    })->name('requests.status');

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
