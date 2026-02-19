<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Firestore\AdoptionsFirestoreService;

class AdoptionsController extends Controller
{
    protected AdoptionsFirestoreService $firebase;

    public function __construct(AdoptionsFirestoreService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Mostrar el formulario de adopciones
     */
    public function form()
    {
        return view('adopciones');
    }

    /**
     * Guardar una nueva adopción
     */
    public function store(Request $request)
    {
        // Verificar que el usuario está autenticado y tiene la role 'refugio'
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión para registrar una adopción'
            ], 401);
        }

        if (!auth()->user()->hasRole('refugio')) {
            return response()->json([
                'success' => false,
                'message' => 'Solo los refugios pueden registrar adopciones'
            ], 403);
        }

        $validated = $request->validate([
            'nombreAnimal' => 'required|string|max:255',
            'tipoAnimal' => 'required|string|max:100',
            'edad' => 'required|integer|min:0|max:50',
            'raza' => 'required|string|max:255',
            'detalles' => 'nullable|string|max:1000',
        ]);

        try {
            $validated['fecha'] = now()->toIso8601String();
            $validated['estado'] = 'pendiente';
            $validated['id'] = uniqid('adoption_');

            $created = $this->firebase->create($validated, $validated['id']);

            return response()->json([
                'success' => true,
                'message' => 'Adopción registrada correctamente',
                'data' => $created
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
            $adopciones = $this->firebase->list();

            return response()->json([
                'success' => true,
                'source' => 'firebase',
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
            $adopcion = $this->firebase->get($id);

            if ($adopcion === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'source' => 'firebase',
                'data' => $adopcion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar únicamente en Firebase (sin tocar el almacenamiento local)
     */
    public function storeToFirebase(Request $request)
    {
        $validated = $request->validate([
            'nombreAnimal' => 'required|string|max:255',
            'tipoAnimal' => 'required|string|max:100',
            'edad' => 'required|integer|min:0|max:50',
            'raza' => 'required|string|max:255',
            'detalles' => 'nullable|string|max:1000',
        ]);

        try {
            $data = $validated;
            $data['fecha'] = now()->toIso8601String();
            $data['estado'] = 'pendiente';
            $data['id'] = uniqid('adoption_');

            $created = $this->firebase->create($data, $data['id']);

            return response()->json([
                'success' => true,
                'message' => 'Adopción enviada a Firebase',
                'data' => $created
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar en Firebase: ' . $e->getMessage()
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
            $updated = $this->firebase->update($id, $validated);
            if (! $updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ], 404);
            }

            $adopcion = $this->firebase->get($id);

            return response()->json([
                'success' => true,
                'message' => 'Adopción actualizada correctamente',
                'data' => $adopcion
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
            $deleted = $this->firebase->delete($id);

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ], 404);
            }

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
