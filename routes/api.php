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

// Rutas públicas para adopciones
Route::prefix('adoptions')->group(function () {
    Route::get('/', [AdoptionsController::class, 'index']);
    Route::get('/{id}', [AdoptionsController::class, 'show']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/', [AdoptionsController::class, 'store']);
    });

    Route::middleware(['auth:sanctum', 'role:admin|veterinario|refugio'])->group(function () {
        Route::post('/firebase', [AdoptionsController::class, 'storeToFirebase']);
        Route::put('/{id}', [AdoptionsController::class, 'update']);
        Route::delete('/{id}', [AdoptionsController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum', 'role:admin|veterinario|refugio'])->group(function () {
        Route::post('/{id}/image', [AdoptionsController::class, 'updateImage']);
    });
});
