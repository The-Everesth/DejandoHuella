<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Firestore\AdoptionsFirestoreService;
use App\Services\Firestore\AdoptionRequestsFirestoreService;
use Illuminate\Support\Str;

class AdoptionsController extends Controller
{
    protected $firebase;
    protected $adoptionRequests;

    public function __construct(AdoptionsFirestoreService $firebase, AdoptionRequestsFirestoreService $adoptionRequests)
    {
        $this->firebase = $firebase;
        $this->adoptionRequests = $adoptionRequests;
    }

    /**
     * Mostrar las solicitudes de adopcion realizadas por el usuario autenticado.
     */
    public function myRequests()
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->hasRole('ciudadano')) {
            abort(403, 'Solo usuarios con rol ciudadano pueden ver sus solicitudes.');
        }

        $requests = collect($this->adoptionRequests->listByApplicant((int) $user->id))->values();

        return view('adoptions.my-requests', [
            'requests' => $requests,
        ]);
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
            $validated['estado'] = 'pendiente';
            $validated['id'] = uniqid('adoption_');
            $validated['createdBy'] = (int) auth()->id();

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
            $data['estado'] = 'pendiente';
            $data['id'] = uniqid('adoption_');
            $data['createdBy'] = (int) auth()->id();

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

    /**
     * Registrar una solicitud de adopción para una mascota publicada
     */
    public function storeRequest(Request $request, string $id)
    {
        $validated = $request->validate([
            'nombreCompleto' => 'required|string|max:255',
            'direccionCiudad' => 'required|string|max:255',
            'tipoVivienda' => 'required|string|in:casa,apartamento,otro',
            'experienciaMascotas' => 'required|string|max:2000',
            'patioJardin' => 'required|string|in:si,no',
            'hogarIntegrantes' => 'required|array|min:1',
            'hogarIntegrantes.*' => 'required|string|in:adultos,ninos,movilidad_reducida,otros',
            'hogarIntegrantesOtros' => 'nullable|string|max:255',
            'tieneOtrosAnimales' => 'required|string|in:si,no',
            'tiposOtrosAnimales' => 'nullable|string|max:255|required_if:tieneOtrosAnimales,si',
            'otrosAnimalesEsterilizados' => 'nullable|string|in:si,no|required_if:tieneOtrosAnimales,si',
            'tuvoMascotasAntes' => 'required|string|in:si,no',
            'detalleMascotasAnteriores' => 'nullable|string|max:2000|required_if:tuvoMascotasAntes,si',
            'dispuestoAtencionVeterinaria' => 'required|string|in:si,no',
            'telefono' => 'required|string|max:40',
            'mensaje' => 'required|string|max:2000',
        ]);

        try {
            $user = auth()->user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes iniciar sesión para solicitar una adopción'
                ], 401);
            }

            if (! $user->hasRole('ciudadano')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo usuarios con rol ciudadano pueden enviar solicitudes de adopción'
                ], 403);
            }

            $adopcion = $this->firebase->get($id);
            if (! is_array($adopcion)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mascota no encontrada'
                ], 404);
            }

            if (isset($adopcion['createdBy']) && (int) $adopcion['createdBy'] === (int) $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes solicitar adopción para una publicación tuya'
                ], 422);
            }

            $hogarIntegrantes = array_values(array_unique($validated['hogarIntegrantes'] ?? []));
            if (in_array('otros', $hogarIntegrantes, true) && blank($validated['hogarIntegrantesOtros'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes especificar quiénes viven en tu hogar cuando seleccionas "Otros"'
                ], 422);
            }

            $solicitud = $this->adoptionRequests->createForAdoption($id, (int) $user->id, [
                'petName' => (string) ($adopcion['nombreAnimal'] ?? ''),
                'applicantName' => (string) ($user->name ?? ''),
                'applicantEmail' => (string) ($user->email ?? ''),
                'nombreCompleto' => (string) $validated['nombreCompleto'],
                'direccionCiudad' => (string) $validated['direccionCiudad'],
                'tipoVivienda' => (string) $validated['tipoVivienda'],
                'experienciaMascotas' => (string) $validated['experienciaMascotas'],
                'patioJardin' => (string) $validated['patioJardin'],
                'hogarIntegrantes' => $hogarIntegrantes,
                'hogarIntegrantesOtros' => isset($validated['hogarIntegrantesOtros']) ? (string) $validated['hogarIntegrantesOtros'] : null,
                'tieneOtrosAnimales' => (string) $validated['tieneOtrosAnimales'],
                'tiposOtrosAnimales' => isset($validated['tiposOtrosAnimales']) ? (string) $validated['tiposOtrosAnimales'] : null,
                'otrosAnimalesEsterilizados' => isset($validated['otrosAnimalesEsterilizados']) ? (string) $validated['otrosAnimalesEsterilizados'] : null,
                'tuvoMascotasAntes' => (string) $validated['tuvoMascotasAntes'],
                'detalleMascotasAnteriores' => isset($validated['detalleMascotasAnteriores']) ? (string) $validated['detalleMascotasAnteriores'] : null,
                'dispuestoAtencionVeterinaria' => (string) $validated['dispuestoAtencionVeterinaria'],
                'telefono' => (string) $validated['telefono'],
                'mensaje' => (string) $validated['mensaje'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud enviada correctamente',
                'data' => $solicitud,
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }
}
