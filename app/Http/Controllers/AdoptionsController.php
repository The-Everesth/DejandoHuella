<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Firestore\AdoptionsFirestoreService;
use Illuminate\Support\Str;

class AdoptionsController extends Controller
{
    protected $firebase;

    public function __construct(AdoptionsFirestoreService $firebase)
    {
        $this->firebase = $firebase;
    }
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
            'fotoMascota' => 'nullable|image|max:4096',
        ]);

        try {
            $validated['fecha'] = now()->toIso8601String();
            $validated['estado'] = 'disponible';
            $validated['id'] = uniqid('adoption_');

            if ($request->hasFile('fotoMascota')) {
                $upload = $request->file('fotoMascota');
                $directory = public_path('uploads/adoptions');
                if (! is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                $filename = Str::uuid()->toString().'.'.$upload->getClientOriginalExtension();
                $upload->move($directory, $filename);

                $validated['imagePath'] = 'uploads/adoptions/'.$filename;
                $validated['imageUrl'] = url('uploads/adoptions/'.$filename);
            }

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
            'fotoMascota' => 'nullable|image|max:4096',
        ]);

        try {
            $data = $validated;
            $data['fecha'] = now()->toIso8601String();
            $data['estado'] = 'disponible';
            $data['id'] = uniqid('adoption_');

            if ($request->hasFile('fotoMascota')) {
                $upload = $request->file('fotoMascota');
                $directory = public_path('uploads/adoptions');
                if (! is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                $filename = Str::uuid()->toString().'.'.$upload->getClientOriginalExtension();
                $upload->move($directory, $filename);

                $data['imagePath'] = 'uploads/adoptions/'.$filename;
                $data['imageUrl'] = url('uploads/adoptions/'.$filename);
            }

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
            $adoption = $this->firebase->get($id);

            $deleted = $this->firebase->delete($id);

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ], 404);
            }

            if (is_array($adoption) && ! empty($adoption['imagePath'])) {
                $fullPath = public_path($adoption['imagePath']);
                if (is_file($fullPath)) {
                    @unlink($fullPath);
                }
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

    /**
     * Actualizar/reemplazar la imagen de una adopción existente
     */
    public function updateImage(Request $request, string $id)
    {
        $request->validate([
            'fotoMascota' => 'required|image|max:4096',
        ]);

        try {
            $adoption = $this->firebase->get($id);

            if (! is_array($adoption)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ], 404);
            }

            if (! $request->hasFile('fotoMascota')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes seleccionar una imagen válida'
                ], 422);
            }

            $upload = $request->file('fotoMascota');
            if (! $upload || ! $upload->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo de imagen no es válido'
                ], 422);
            }

            $directory = public_path('uploads/adoptions');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $filename = Str::uuid()->toString().'.'.$upload->getClientOriginalExtension();
            $upload->move($directory, $filename);

            $newImagePath = 'uploads/adoptions/'.$filename;
            $newImageUrl = url($newImagePath);

            $updated = $this->firebase->update($id, [
                'imagePath' => $newImagePath,
                'imageUrl' => $newImageUrl,
            ]);

            if (! $updated) {
                $newFullPath = public_path($newImagePath);
                if (is_file($newFullPath)) {
                    @unlink($newFullPath);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo actualizar la imagen'
                ], 422);
            }

            if (! empty($adoption['imagePath'])) {
                $oldFullPath = public_path($adoption['imagePath']);
                if (is_file($oldFullPath)) {
                    @unlink($oldFullPath);
                }
            }

            $fresh = $this->firebase->get($id);

            return response()->json([
                'success' => true,
                'message' => 'Imagen actualizada correctamente',
                'data' => $fresh,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar imagen: '.$e->getMessage(),
            ], 500);
        }
    }
}
