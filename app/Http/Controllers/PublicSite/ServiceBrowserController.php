<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\MedicalService;
use App\Models\User;
use App\Services\Firestore\ClinicsFirestoreService;
use App\Services\Firestore\MedicalServicesFirestoreService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceBrowserController extends Controller
{
    public function __construct(
        protected MedicalServicesFirestoreService $servicesFirestore,
        protected ClinicsFirestoreService $clinicsFirestore
    )
    {
    }

    public function index(Request $request)
    {
        $serviceId = $request->query('service_id');
        $q = trim((string) $request->query('q', ''));
        $firestoreServices = $this->servicesFirestore->listActiveServices();
        $servicesById = collect($firestoreServices)
            ->mapWithKeys(function (array $service) {
                $id = $service['id'] ?? null;
                return $id ? [$id => $service] : [];
            });

        $services = $servicesById
            ->map(fn (array $service) => (object) [
                'id' => $service['id'],
                'name' => $service['name'] ?? ($service['title'] ?? 'Sin nombre'),
            ])
            ->values();

        // Fallback para proyectos aún sin catálogo en Firestore.
        if ($services->isEmpty()) {
            $services = MedicalService::query()
                ->when(\Illuminate\Support\Facades\Schema::hasColumn('medical_services', 'is_active'), fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->get();

            $servicesById = $services->keyBy('id')->map(fn ($service) => [
                'id' => (string) $service->id,
                'name' => $service->name,
            ]);
        }

        $rawClinics = collect($this->clinicsFirestore->listPublishedClinics([
            'serviceId' => $serviceId,
            'q' => $q,
        ]));

        $allClinics = $rawClinics->map(function (array $clinicData) use ($servicesById) {
            $docId = $clinicData['id'] ?? ($clinicData['_docId'] ?? null);
            $ownerId = $clinicData['ownerUserId'] ?? $clinicData['userId'] ?? $clinicData['mysqlUserId'] ?? null;
            $user = $ownerId ? User::find((int) $ownerId) : null;

            $serviceIds = $clinicData['serviceIds'] ?? ($clinicData['services'] ?? []);
            $serviceItems = collect(is_array($serviceIds) ? $serviceIds : [])->map(function ($id) use ($servicesById) {
                $service = $servicesById->get($id);
                if (! $service) {
                    return null;
                }

                return (object) [
                    'id' => $service['id'] ?? $id,
                    'name' => $service['name'] ?? ($service['title'] ?? 'Servicio'),
                    'title' => $service['name'] ?? ($service['title'] ?? 'Servicio'),
                ];
            })->filter()->values();

            $clinic = (object) [
                'id' => $docId,
                'name' => $clinicData['name'] ?? 'Clínica',
                'address' => $clinicData['address'] ?? ($clinicData['address_line'] ?? null),
                'services' => $serviceItems,
                'user' => $user,
                'firestore_id' => $docId,
            ];

            return $clinic;
        })->filter(fn ($clinic) => ! empty($clinic->id))->values();

        $perPage = 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pageItems = $allClinics->forPage($currentPage, $perPage)->values();

        $clinics = new LengthAwarePaginator(
            $pageItems,
            $allClinics->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('services.index', compact('services', 'clinics', 'serviceId', 'q'));
    }

    public function clinics(MedicalService $service)
    {
        $service->load(['clinics' => function ($query) {
            $query->orderBy('name');
        }]);

        return view('services.clinics', compact('service'));
    }
}
