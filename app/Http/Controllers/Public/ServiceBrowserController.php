<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MedicalService;

class ServiceBrowserController extends Controller
{
    public function index($request)
{
    $serviceId = $request->query('service_id');
    $q = trim((string)$request->query('q',''));

    $services = \App\Models\MedicalService::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    $clinics = \App\Models\Clinic::query()
        ->where('is_public', true)
        ->whereHas('user', function($u){
            // SoftDeletes scope ya excluye dados de baja automáticamente
            $u->whereHas('roles', fn($r) => $r->where('name','veterinario'));
        })
        ->when($q !== '', function($query) use ($q){
            $query->where(function($sub) use ($q){
                $sub->where('name','like',"%{$q}%")
                    ->orWhere('address','like',"%{$q}%");
            });
        })
        ->when($serviceId, function($query) use ($serviceId){
            $query->whereHas('services', fn($s)=>$s->where('medical_services.id',$serviceId));
        })
        ->with(['services'])
        ->latest()
        ->paginate(12)
        ->appends(request()->query());


    return view('public.services.index', compact('services','clinics','serviceId','q'));
}


    public function clinics(MedicalService $service)
    {
        $service->load(['clinics' => function ($q) {
            $q->orderBy('name');
        }]);

        return view('services.clinics', compact('service'));
    }
}
