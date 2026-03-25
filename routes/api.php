<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdoptionsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Ruta pública para estadísticas del sistema (home page)
Route::get('/system-stats', function () {
    try {
        $adoptionsService = app(\App\Services\Firestore\AdoptionsFirestoreService::class);
        $usersService = app(\App\Services\Firestore\UsersFirestoreService::class);
        $clinicsService = app(\App\Services\Firestore\ClinicsFirestoreService::class);

        $adoptionsCount = count($adoptionsService->list());
        $usersCount = count($usersService->list());
        $clinicsCount = count($clinicsService->list());

        return response()->json([
            'users_count' => $usersCount,
            'adoptions_count' => $adoptionsCount,
            'clinics_count' => $clinicsCount,
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::warning('Error loading system stats: ' . $e->getMessage());
        return response()->json([
            'users_count' => 0,
            'adoptions_count' => 0,
            'clinics_count' => 0,
        ]);
    }
});

// Rutas públicas para adopciones
Route::prefix('adoptions')->group(function () {
    Route::get('/', [AdoptionsController::class, 'index']);
    Route::get('/{id}', [AdoptionsController::class, 'show']);
    Route::middleware(['auth:sanctum', 'role:veterinario|refugio'])->group(function () {
        Route::post('/', [AdoptionsController::class, 'store']);
    });

    Route::middleware(['auth:sanctum', 'role:ciudadano'])->group(function () {
        Route::post('/{id}/request', [AdoptionsController::class, 'storeRequest']);
    });

    Route::middleware(['auth:sanctum', 'role:veterinario|refugio'])->group(function () {
        Route::post('/firebase', [AdoptionsController::class, 'storeToFirebase']);
        Route::put('/{id}', [AdoptionsController::class, 'update']);
        Route::delete('/{id}', [AdoptionsController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum', 'role:veterinario|refugio'])->group(function () {
        Route::post('/{id}/image', [AdoptionsController::class, 'updateImage']);
    });
});
