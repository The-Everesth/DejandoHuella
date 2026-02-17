<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdoptionsController extends Controller
{
    /**
     * Guardar una nueva adopción
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombreAnimal' => 'required|string|max:255',
            'tipoAnimal' => 'required|string|max:100',
            'edad' => 'required|integer|min:0|max:50',
            'raza' => 'required|string|max:255',
            'detalles' => 'nullable|string|max:1000',
        ]);

        try {
            // Agregar información adicional
            $validated['fecha'] = now()->toIso8601String();
            $validated['estado'] = 'pendiente';
            $validated['id'] = uniqid('adoption_');

            // Leer adopciones existentes
            $filePath = 'adopciones.json';
            $adopciones = [];
            
            if (Storage::disk('local')->exists($filePath)) {
                $adopciones = json_decode(Storage::disk('local')->get($filePath), true) ?? [];
            }

            // Agregar nueva adopción
            $adopciones[$validated['id']] = $validated;

            // Guardar en archivo
            Storage::disk('local')->put($filePath, json_encode($adopciones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return response()->json([
                'success' => true,
                'message' => 'Adopción registrada correctamente',
                'data' => $validated
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar adopción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todas las adopciones
     */
    public function index()
    {
        try {
            $filePath = 'adopciones.json';
            
            if (!Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $adopciones = json_decode(Storage::disk('local')->get($filePath), true) ?? [];

            return response()->json([
                'success' => true,
                'data' => $adopciones
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener adopciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una adopción específica
     */
    public function show(string $id)
    {
        try {
            $filePath = 'adopciones.json';

            if (!Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ], 404);
            }

            $adopciones = json_decode(Storage::disk('local')->get($filePath), true) ?? [];

            if (!isset($adopciones[$id])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $adopciones[$id]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar una adopción
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nombreAnimal' => 'sometimes|string|max:255',
            'tipoAnimal' => 'sometimes|string|max:100',
            'edad' => 'sometimes|integer|min:0|max:50',
            'raza' => 'sometimes|string|max:255',
            'detalles' => 'nullable|string|max:1000',
            'estado' => 'sometimes|string|max:50',
        ]);

        try {
            $filePath = 'adopciones.json';

            if (!Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ], 404);
            }

            $adopciones = json_decode(Storage::disk('local')->get($filePath), true) ?? [];

            if (!isset($adopciones[$id])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ], 404);
            }

            // Actualizar adopción
            $adopciones[$id] = array_merge($adopciones[$id], $validated);

            // Guardar
            Storage::disk('local')->put($filePath, json_encode($adopciones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return response()->json([
                'success' => true,
                'message' => 'Adopción actualizada correctamente',
                'data' => $adopciones[$id]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una adopción
     */
    public function destroy(string $id)
    {
        try {
            $filePath = 'adopciones.json';

            if (!Storage::disk('local')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ], 404);
            }

            $adopciones = json_decode(Storage::disk('local')->get($filePath), true) ?? [];

            if (!isset($adopciones[$id])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ], 404);
            }

            unset($adopciones[$id]);

            Storage::disk('local')->put($filePath, json_encode($adopciones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return response()->json([
                'success' => true,
                'message' => 'Adopción eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
