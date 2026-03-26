<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\User;
use App\Services\Firestore\AdoptionsFirestoreService;
use App\Services\Firestore\AdoptionRequestsFirestoreService;
use App\Services\CloudinaryService;
use Illuminate\Support\Str;


class AdoptionsController extends Controller
{
    protected $firebase;
    protected $adoptionRequests;
    protected array $allowedPublisherCache = [];
    protected ?array $citizenVisibleAdoptionLookup = null;

    /**
     * Robust role check for user (handles array, object, Firestore, etc)
     */
    protected function userHasRole($user, $roles): bool
    {
        if (is_object($user) && method_exists($user, 'hasRole')) {
            return $user->hasRole($roles);
        }
        $userId = is_object($user) ? ($user->id ?? null) : ($user['id'] ?? null);
        if (!$userId) return false;
        $usersService = app(\App\Services\Firestore\UsersFirestoreService::class);
        $docId = $usersService->getUserDocId((int)$userId);
        $firestoreUser = $usersService->getUserByDocId($docId);
        $userRoles = $firestoreUser['roles'] ?? [$firestoreUser['role'] ?? null];
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if (in_array($role, $userRoles)) return true;
            }
            return false;
        }
        return in_array($roles, $userRoles);
    }

    protected function userHasAnyRole($user, ...$roles): bool
    {
        return $this->userHasRole($user, $roles);
    }

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

        if (! $this->userHasRole($user, 'ciudadano')) {
            abort(403, 'Solo usuarios con rol ciudadano pueden ver sus solicitudes.');
        }

        $visibleAdoptions = $this->getCitizenVisibleAdoptionLookup();

        $requests = collect($this->adoptionRequests->listByApplicant((int) $user->id))
            ->filter(function (array $item) use ($visibleAdoptions): bool {
                $adoptionId = trim((string) ($item['adoptionId'] ?? ''));

                return $adoptionId !== '' && isset($visibleAdoptions[$adoptionId]);
            })
            ->values();

        return view('adoptions.my-requests', [
            'requests' => $requests,
        ]);
    }

    /**
     * Devolver IDs de adopciones que ya fueron solicitadas por el ciudadano autenticado.
     */
    public function myRequestedAdoptionIds()
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión.',
            ], 401);
        }

        if (! $this->userHasRole($user, 'ciudadano')) {
            abort(403, 'Solo usuarios con rol ciudadano pueden consultar sus solicitudes.');
        }

        $visibleAdoptions = $this->getCitizenVisibleAdoptionLookup();

        $adoptionIds = collect($this->adoptionRequests->listByApplicant((int) $user->id))
            ->filter(static function (array $item): bool {
                $status = strtolower(trim((string) ($item['status'] ?? 'pendiente')));
                return ! in_array($status, ['cancelada', 'cancelled', 'canceled'], true);
            })
            ->map(static function (array $item): string {
                return trim((string) ($item['adoptionId'] ?? ''));
            })
            ->filter(function (string $adoptionId) use ($visibleAdoptions): bool {
                return $adoptionId !== '' && isset($visibleAdoptions[$adoptionId]);
            })
            ->unique()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $adoptionIds,
        ]);
    }

    /**
     * Permitir al ciudadano cancelar una solicitud propia de adopcion.
     */
    public function cancelMyRequest(string $requestId)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $this->userHasRole($user, 'ciudadano')) {
            abort(403, 'Solo usuarios con rol ciudadano pueden cancelar sus solicitudes.');
        }

        $solicitud = $this->adoptionRequests->get($requestId);
        if (! is_array($solicitud)) {
            return back()->with('error', 'La solicitud no fue encontrada.');
        }

        $applicantId = (int) ($solicitud['applicantId'] ?? 0);
        if ($applicantId !== (int) $user->id) {
            abort(403, 'No tienes permisos para cancelar esta solicitud.');
        }

        $currentStatus = strtolower(trim((string) ($solicitud['status'] ?? 'pendiente')));
        $isApproved = in_array($currentStatus, ['aprobada', 'approved'], true);
        $isRejected = in_array($currentStatus, ['rechazada', 'rejected'], true);
        $isCancelled = in_array($currentStatus, ['cancelada', 'cancelled', 'canceled'], true);

        if ($isCancelled) {
            return back()->with('success', 'La solicitud ya estaba cancelada.');
        }

        if ($isApproved || $isRejected) {
            return back()->with('error', 'Solo puedes cancelar solicitudes pendientes.');
        }

        $updated = $this->adoptionRequests->setStatus($requestId, 'cancelada', [
            'cancelledAt' => now()->toIso8601String(),
            'cancelledBy' => (int) $user->id,
        ]);

        if (! $updated) {
            return back()->with('error', 'No se pudo cancelar la solicitud. Intenta de nuevo.');
        }

        return back()->with('success', 'Solicitud cancelada correctamente.');
    }

    /**
     * Mostrar las adopciones publicadas por el usuario autenticado.
     */
    public function vetMyAdoptions()
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $this->userHasAnyRole($user, 'veterinario', 'refugio')) {
            abort(403, 'Solo usuarios con rol veterinario o refugio pueden ver sus adopciones.');
        }

        $myAdoptions = [];
        foreach ($this->firebase->list() as $docId => $adoption) {
            if ((int) ($adoption['createdBy'] ?? 0) !== (int) $user->id) {
                continue;
            }

            $adoptionId = (string) ($adoption['id'] ?? $adoption['_docId'] ?? $docId);
            if ($adoptionId === '') {
                continue;
            }

            $adoption['id'] = $adoptionId;
            $myAdoptions[] = $adoption;
        }

        usort($myAdoptions, static function (array $a, array $b): int {
            return strcmp((string) ($b['fecha'] ?? ''), (string) ($a['fecha'] ?? ''));
        });

        return view('adoptions.my-adoptions', [
            'adoptions' => $myAdoptions,
        ]);
    }

    /**
     * Mostrar las solicitudes de adopcion recibidas en publicaciones del usuario.
     */
    public function publishedRequests()
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Compatibilidad legacy: si admin entra por la ruta vieja,
        // lo enviamos al nuevo módulo de moderación de publicaciones.
        if ($this->userHasRole($user, 'admin')) {
            return redirect()->route('admin.adoptions.index');
        }

        if (! $this->userHasAnyRole($user, 'veterinario', 'refugio')) {
            abort(403, 'Solo usuarios con rol veterinario o refugio pueden ver solicitudes recibidas.');
        }

        $ownedAdoptions = [];
        foreach ($this->firebase->list() as $docId => $adoption) {
            if ((int) ($adoption['createdBy'] ?? 0) !== (int) $user->id) {
                continue;
            }

            $adoptionId = (string) ($adoption['id'] ?? $adoption['_docId'] ?? $docId);
            if ($adoptionId === '') {
                continue;
            }

            $adoption['id'] = $adoptionId;
            $ownedAdoptions[$adoptionId] = $adoption;
        }

        $requests = collect($this->adoptionRequests->listByAdoptionIds(array_keys($ownedAdoptions)))
            ->map(function (array $item) use ($ownedAdoptions): array {
                $adoptionId = (string) ($item['adoptionId'] ?? '');
                $adoption = $ownedAdoptions[$adoptionId] ?? [];

                if (empty($item['petName']) && ! empty($adoption['nombreAnimal'])) {
                    $item['petName'] = (string) $adoption['nombreAnimal'];
                }

                if (empty($item['petType']) && ! empty($adoption['tipoAnimal'])) {
                    $item['petType'] = (string) $adoption['tipoAnimal'];
                }

                if (empty($item['petSex']) && ! empty($adoption['sexo'])) {
                    $item['petSex'] = (string) $adoption['sexo'];
                }

                return $item;
            })
            ->values();

        return view('adoptions.published-requests', [
            'requests' => $requests,
        ]);
    }

    /**
     * Aprobar o rechazar una solicitud de adopcion.
     */
    public function updateRequestStatus(Request $request, string $requestId)
    {
        $user = auth()->user();
        if ($user && $this->userHasRole($user, 'admin')) {
            abort(403, 'El rol admin solo puede visualizar el estado de las solicitudes.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:aprobada,rechazada',
        ]);

        $solicitud = $this->adoptionRequests->get($requestId);
        if (! is_array($solicitud)) {
            return back()->with('error', 'La solicitud no fue encontrada.');
        }

        $currentStatus = strtolower(trim((string) ($solicitud['status'] ?? 'pendiente')));
        if (in_array($currentStatus, ['cancelada', 'cancelled', 'canceled'], true)) {
            return back()->with('error', 'No se puede gestionar una solicitud cancelada por el ciudadano.');
        }

        $adoptionId = (string) ($solicitud['adoptionId'] ?? '');
        if ($adoptionId === '') {
            return back()->with('error', 'La solicitud no tiene una publicación asociada válida.');
        }

        $adopcion = $this->firebase->get($adoptionId);
        if (! is_array($adopcion)) {
            return back()->with('error', 'La publicación asociada no fue encontrada.');
        }

        if (! $this->canCurrentUserManageAdoption($adopcion)) {
            abort(403, 'No tienes permisos para cambiar el estado de esta solicitud.');
        }

        $this->adoptionRequests->setStatus($requestId, (string) $validated['status'], [
            'reviewedAt' => now()->toIso8601String(),
            'reviewedBy' => $user ? (int) $user->id : null,
        ]);

        $statusLabel = $validated['status'] === 'aprobada' ? 'aprobada' : 'rechazada';

        return back()->with('success', 'La solicitud fue '.$statusLabel.' correctamente.');
    }

    /**
     * Guardar una nota visible para el ciudadano en una solicitud de adopcion.
     */
    public function updateRequestNote(Request $request, string $requestId)
    {
        $user = auth()->user();
        if ($user && $this->userHasRole($user, 'admin')) {
            abort(403, 'El rol admin solo puede visualizar el estado de las solicitudes.');
        }

        $validated = $request->validate([
            'reviewerNote' => 'required|string|max:1000',
        ]);

        $solicitud = $this->adoptionRequests->get($requestId);
        if (! is_array($solicitud)) {
            return back()->with('error', 'La solicitud no fue encontrada.');
        }

        $currentStatus = strtolower(trim((string) ($solicitud['status'] ?? 'pendiente')));
        if (in_array($currentStatus, ['cancelada', 'cancelled', 'canceled'], true)) {
            return back()->with('error', 'No se puede agregar una nota a una solicitud cancelada.');
        }

        $adoptionId = (string) ($solicitud['adoptionId'] ?? '');
        if ($adoptionId === '') {
            return back()->with('error', 'La solicitud no tiene una publicacion asociada valida.');
        }

        $adopcion = $this->firebase->get($adoptionId);
        if (! is_array($adopcion)) {
            return back()->with('error', 'La publicacion asociada no fue encontrada.');
        }

        if (! $this->canCurrentUserManageAdoption($adopcion)) {
            abort(403, 'No tienes permisos para editar esta solicitud.');
        }

        $saved = $this->adoptionRequests->setStatus($requestId, (string) ($solicitud['status'] ?? 'pendiente'), [
            'reviewerNote' => trim((string) $validated['reviewerNote']),
            'reviewerNoteAt' => now()->toIso8601String(),
            'reviewerNoteBy' => $user ? (int) $user->id : null,
        ]);

        if (! $saved) {
            return back()->with('error', 'No se pudo guardar la nota. Intenta de nuevo.');
        }

        return back()->with('success', 'Nota guardada correctamente.');
    }

    /**
     * Guardar una nueva adopción
     */
    public function store(Request $request)
    {
        $roleError = $this->ensurePublisherRole();
        if ($roleError) {
            return $roleError;
        }

        $validated = $request->validate([
            'nombreAnimal' => 'required|string|max:255',
            'tipoAnimal' => 'required|string|max:100',
            'sexo' => 'required|string|in:hembra,macho',
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

            // Subir la imagen a Cloudinary si existe
            if ($request->hasFile('fotoMascota')) {
                $photoFile = $request->file('fotoMascota');
                if ($photoFile && $photoFile->isValid()) {
                    $cloudinary = app(CloudinaryService::class);
                    $secureUrl = $cloudinary->uploadImage($photoFile, 'adoptions');
                    if ($secureUrl) {
                        $validated['imageUrl'] = $secureUrl;
                    }
                }
            }

            unset($validated['fotoMascota']); // Nunca guardar la ruta temporal ni el archivo
            unset($validated['imagePath']); // No usar almacenamiento local

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
            $adopciones = array_filter(
                $this->firebase->list(),
                function (array $adopcion): bool {
                    return ! $this->isAdoptionHidden($adopcion)
                        && $this->isAllowedPublisher($adopcion);
                }
            );

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

            if ($adopcion === null
                || $this->isAdoptionHidden($adopcion)
                || ! $this->isAllowedPublisher($adopcion)) {
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
        $roleError = $this->ensurePublisherRole();
        if ($roleError) {
            return $roleError;
        }

        $validated = $request->validate([
            'nombreAnimal' => 'required|string|max:255',
            'tipoAnimal' => 'required|string|max:100',
            'sexo' => 'required|string|in:hembra,macho',
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
        $roleError = $this->ensurePublisherRole();
        if ($roleError) {
            return $roleError;
        }

        $validated = $request->validate([
            'nombreAnimal' => 'sometimes|string|max:255',
            'tipoAnimal' => 'sometimes|string|max:100',
            'sexo' => 'sometimes|string|in:hembra,macho',
            'edad' => 'sometimes|integer|min:0|max:50',
            'raza' => 'sometimes|string|max:255',
            'detalles' => 'nullable|string|max:1000',
            'estado' => 'sometimes|string|max:50',
            'fotoMascota' => 'nullable|image|max:4096',
        ]);

        try {
            $adopcion = $this->firebase->get($id);
            if (! is_array($adopcion)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ], 404);
            }

            $authorizationError = $this->forbidIfCannotManageAdoption($adopcion);
            if ($authorizationError) {
                return $authorizationError;
            }

            // Si viene una nueva foto en el formulario de edición, la reemplazamos
            // y actualizamos también las rutas persistidas en Firebase.
            if ($request->hasFile('fotoMascota')) {
                $upload = $request->file('fotoMascota');
                if ($upload && $upload->isValid()) {
                    $directory = public_path('uploads/adoptions');
                    if (! is_dir($directory)) {
                        mkdir($directory, 0755, true);
                    }

                    $filename = Str::uuid()->toString().'.'.$upload->getClientOriginalExtension();
                    $upload->move($directory, $filename);

                    if (! empty($adopcion['imagePath'])) {
                        $oldImagePath = public_path((string) $adopcion['imagePath']);
                        if (is_file($oldImagePath)) {
                            @unlink($oldImagePath);
                        }
                    }

                    $validated['imagePath'] = 'uploads/adoptions/'.$filename;
                    $validated['imageUrl'] = url('uploads/adoptions/'.$filename);
                }
            }

            unset($validated['fotoMascota']);

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
        $roleError = $this->ensurePublisherRole();
        if ($roleError) {
            return $roleError;
        }

        try {
            $adoption = $this->firebase->get($id);

            if (! is_array($adoption)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ], 404);
            }

            $authorizationError = $this->forbidIfCannotManageAdoption($adoption);
            if ($authorizationError) {
                return $authorizationError;
            }

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
        $roleError = $this->ensurePublisherRole();
        if ($roleError) {
            return $roleError;
        }

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

            $authorizationError = $this->forbidIfCannotManageAdoption($adoption);
            if ($authorizationError) {
                return $authorizationError;
            }

            if (! $request->hasFile('fotoMascota')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes seleccionar una imagen válida'
                ], 422);
            }

            $photoFile = $request->file('fotoMascota');
            if (! $photoFile || ! $photoFile->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo de imagen no es válido'
                ], 422);
            }

            $cloudinary = app(CloudinaryService::class);
            $secureUrl = $cloudinary->uploadImage($photoFile, 'adoptions');
            if (! $secureUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo subir la imagen a Cloudinary'
                ], 422);
            }

            $updated = $this->firebase->update($id, [
                'imageUrl' => $secureUrl,
            ]);

            if (! $updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo actualizar la imagen'
                ], 422);
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
            $roleError = $this->ensurePublisherRole();
            if ($roleError) {
                return $roleError;
            }

            $validated = $request->validate([
                'nombreAnimal' => 'sometimes|string|max:255',
                'tipoAnimal' => 'sometimes|string|max:100',
                'sexo' => 'sometimes|string|in:hembra,macho',
                'edad' => 'sometimes|integer|min:0|max:50',
                'raza' => 'sometimes|string|max:255',
                'detalles' => 'nullable|string|max:1000',
                'estado' => 'sometimes|string|max:50',
                'fotoMascota' => 'nullable|image|max:4096',
            ]);

            try {
                $adopcion = $this->firebase->get($id);
                if (! is_array($adopcion)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Adopción no encontrada'
                    ], 404);
                }

                $authorizationError = $this->forbidIfCannotManageAdoption($adopcion);
                if ($authorizationError) {
                    return $authorizationError;
                }

                // Si viene una nueva foto en el formulario de edición, la subimos a Cloudinary
                if ($request->hasFile('fotoMascota')) {
                    $photoFile = $request->file('fotoMascota');
                    if ($photoFile && $photoFile->isValid()) {
                        $cloudinary = app(CloudinaryService::class);
                        $secureUrl = $cloudinary->uploadImage($photoFile, 'adoptions');
                        if ($secureUrl) {
                            $validated['imageUrl'] = $secureUrl;
                        }
                    }
                }

                unset($validated['fotoMascota']); // Nunca guardar la ruta temporal ni el archivo
                unset($validated['imagePath']); // No usar almacenamiento local

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

    /**
     * Solo usuarios con rol veterinario o refugio pueden gestionar publicaciones.
     */
    protected function ensurePublisherRole()
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión para gestionar publicaciones de adopción'
            ], 401);
        }

        if (! $this->userHasAnyRole($user, 'veterinario', 'refugio')) {
            return response()->json([
                'success' => false,
                'message' => 'Solo usuarios con rol veterinario o refugio pueden publicar y gestionar adopciones'
            ], 403);
        }

        return null;
    }

    /**
     * Solo el publicador de la mascota puede modificar la publicación.
     */
    protected function forbidIfCannotManageAdoption(array $adoption)
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión para modificar esta adopción'
            ], 401);
        }

        if (! $this->canCurrentUserManageAdoption($adoption)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el usuario que publicó esta mascota puede modificarla'
            ], 403);
        }

        return null;
    }

    protected function canCurrentUserManageAdoption(array $adoption): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if (! $this->userHasAnyRole($user, 'veterinario', 'refugio')) {
            return false;
        }

        $ownerId = (int) ($adoption['createdBy'] ?? 0);

        return $ownerId === (int) $user->id;
    }

    protected function isAdoptionHidden(array $adoption): bool
    {
        $value = $adoption['isHidden'] ?? false;

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'si', 'sí'], true);
    }

    protected function isAllowedPublisher(array $adoption): bool
    {
        $publisherId = (int) ($adoption['createdBy'] ?? 0);
        if ($publisherId <= 0) {
            // Documentos legacy de Firestore pueden no traer owner local.
            // Los dejamos visibles para no ocultar publicaciones válidas.
            return true;
        }

        if (array_key_exists($publisherId, $this->allowedPublisherCache)) {
            return $this->allowedPublisherCache[$publisherId];
        }

        // Firestore-based user lookup by Laravel user ID
        $usersService = app(\App\Services\Firestore\UsersFirestoreService::class);
        $docId = $usersService->getUserDocId($publisherId);
        $publisher = $usersService->getUserByDocId($docId);
        $roles = $publisher['roles'] ?? [$publisher['role'] ?? null];
        $isAllowed = $publisher
            ? (is_array($roles) && (in_array('veterinario', $roles) || in_array('refugio', $roles)))
            : true;

        $this->allowedPublisherCache[$publisherId] = $isAllowed;

        return $isAllowed;
    }

    protected function getCitizenVisibleAdoptionLookup(): array
    {
        if (is_array($this->citizenVisibleAdoptionLookup)) {
            return $this->citizenVisibleAdoptionLookup;
        }

        $lookup = [];

        foreach ($this->firebase->list() as $docId => $adoption) {
            $adoptionId = trim((string) ($adoption['id'] ?? $adoption['_docId'] ?? $docId));
            if ($adoptionId === '') {
                continue;
            }

            if ($this->isAdoptionHidden($adoption) || ! $this->isAllowedPublisher($adoption)) {
                continue;
            }

            $lookup[$adoptionId] = true;
        }

        $this->citizenVisibleAdoptionLookup = $lookup;

        return $this->citizenVisibleAdoptionLookup;
    }
}
