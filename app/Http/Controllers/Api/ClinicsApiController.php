<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Firestore\ClinicsFirestoreService;

class ClinicsApiController extends Controller
{
    protected ClinicsFirestoreService $firebase;

    public function __construct(ClinicsFirestoreService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Devuelve todas las clínicas (publish=true) desde Firestore.
     */
    public function index(Request $request)
    {
        try {
            // soportar filtros parecidos a listPublishedClinics
            $filters = [];
            if ($request->has('serviceId')) {
                $filters['serviceId'] = $request->query('serviceId');
            }
            if ($request->has('q')) {
                $filters['q'] = $request->query('q');
            }

            $clinics = $this->firebase->listPublishedClinics($filters);

            return response()->json([
                'success' => true,
                'source' => 'firebase',
                'data' => $clinics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clínicas: ' . $e->getMessage(),
            ], 500);
        }
    }
}
