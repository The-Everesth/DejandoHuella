<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\MedicalService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceBrowserController extends Controller
{
    public function index(Request $request)
    {
        $serviceId = $request->query('service_id');
        $q = trim((string) $request->query('q', ''));

        $services = MedicalService::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $clinicsQuery = Clinic::query()
            ->where('is_public', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('address', 'like', "%{$q}%");
                });
            })
            ->when($serviceId, function ($query) use ($serviceId) {
                $query->whereHas('services', function ($serviceQuery) use ($serviceId) {
                    $serviceQuery->where('medical_services.id', $serviceId);
                });
            })
            ->with(['services', 'user'])
            ->latest();

        $allClinics = $clinicsQuery->get()->filter(function ($clinic) {
            return $clinic->user && $clinic->user->hasRole('veterinario');
        })->values();

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
