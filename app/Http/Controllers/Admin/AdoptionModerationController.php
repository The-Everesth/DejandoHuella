<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Firestore\AdoptionsFirestoreService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdoptionModerationController extends Controller
{
    protected AdoptionsFirestoreService $adoptions;

    public function __construct(AdoptionsFirestoreService $adoptions)
    {
        $this->adoptions = $adoptions;
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $type = strtolower(trim((string) $request->query('type', '')));
        $publisherRole = strtolower(trim((string) $request->query('publisher_role', '')));
        $visibility = $request->query('visibility');

        $allAdoptions = $this->buildModeratedAdoptions();

        $summary = [
            'active' => $allAdoptions->where('is_hidden', false)->count(),
            'hidden' => $allAdoptions->where('is_hidden', true)->count(),
            'all' => $allAdoptions->count(),
        ];

        $publisherRoleOptions = $allAdoptions
            ->pluck('publisher_role')
            ->filter(function (string $role): bool {
                return $role !== '';
            })
            ->unique()
            ->sort()
            ->values();

        $publisherRoleLabels = [
            'admin' => 'Admin',
            'veterinario' => 'Veterinaria',
            'refugio' => 'Refugio',
            'ciudadano' => 'Ciudadano',
            'sin rol' => 'Sin rol',
        ];

        $filteredAdoptions = $allAdoptions
            ->when($visibility === 'hidden', function ($items) {
                return $items->where('is_hidden', true);
            })
            ->when(! in_array($visibility, ['hidden', 'with'], true), function ($items) {
                return $items->where('is_hidden', false);
            })
            ->when($q !== '', function ($items) use ($q) {
                $needle = mb_strtolower($q);

                return $items->filter(function (array $adoption) use ($needle): bool {
                    $haystacks = [
                        mb_strtolower($adoption['pet_name']),
                        mb_strtolower($adoption['publisher_name']),
                        mb_strtolower($adoption['publisher_email']),
                    ];

                    foreach ($haystacks as $haystack) {
                        if ($haystack !== '' && str_contains($haystack, $needle)) {
                            return true;
                        }
                    }

                    return false;
                });
            })
            ->when($type !== '', function ($items) use ($type) {
                return $items->where('pet_type_value', $type);
            })
            ->when($publisherRole !== '', function ($items) use ($publisherRole) {
                return $items->where('publisher_role', $publisherRole);
            })
            ->values();

        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pageItems = $filteredAdoptions->forPage($currentPage, $perPage)->values();

        $adoptions = new LengthAwarePaginator(
            $pageItems,
            $filteredAdoptions->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.adoptions.index', compact(
            'adoptions',
            'q',
            'type',
            'publisherRole',
            'visibility',
            'summary',
            'publisherRoleOptions',
            'publisherRoleLabels'
        ));
    }

    public function updateVisibility(Request $request, string $adoptionId)
    {
        $request->validate([
            'is_hidden' => ['required', 'boolean'],
        ]);

        $adoption = $this->adoptions->get($adoptionId);
        if (! is_array($adoption)) {
            return back()->withErrors(['adoption' => 'La publicacion no fue encontrada.']);
        }

        $shouldHide = $request->boolean('is_hidden');

        $payload = [
            'isHidden' => $shouldHide,
        ];

        if ($shouldHide) {
            $payload['hiddenAt'] = now()->toIso8601String();
            $payload['hiddenBy'] = (int) auth()->id();
        } else {
            $payload['hiddenAt'] = null;
            $payload['hiddenBy'] = null;
        }

        $updated = $this->adoptions->update($adoptionId, $payload);
        if (! $updated) {
            return back()->withErrors(['adoption' => 'No se pudo actualizar la visibilidad de la publicacion.']);
        }

        return redirect()
            ->route('admin.adoptions.index', $request->only(['visibility', 'q', 'type', 'publisher_role', 'page']))
            ->with('success', $shouldHide
                ? 'Publicacion ocultada correctamente.'
                : 'Publicacion visible nuevamente.');
    }

    protected function buildModeratedAdoptions()
    {
        $documents = collect($this->adoptions->list())
            ->map(function (array $adoption, string $docId): array {
                $adoptionId = (string) ($adoption['id'] ?? $adoption['_docId'] ?? $docId);
                $adoption['id'] = $adoptionId;

                return $adoption;
            })
            ->values();

        $publisherIds = $documents
            ->pluck('createdBy')
            ->filter(function ($id): bool {
                return is_numeric($id) && (int) $id > 0;
            })
            ->map(function ($id): int {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();

        $users = User::withTrashed()
            ->whereIn('id', $publisherIds)
            ->get()
            ->keyBy('id');

        return $documents
            ->map(function (array $adoption) use ($users): array {
                $publisherId = (int) ($adoption['createdBy'] ?? 0);
                $publisher = $users->get($publisherId);
                $publisherRoles = $publisher
                    ? $publisher->getRoleNames()->map(function ($role): string {
                        return strtolower((string) $role);
                    })->values()
                    : collect();

                $publisherRole = (string) ($publisherRoles->first(function (string $role): bool {
                    return in_array($role, ['veterinario', 'refugio'], true);
                }) ?? 'sin rol');

                $createdAt = (string) ($adoption['fecha'] ?? '');

                return [
                    'id' => (string) ($adoption['id'] ?? ''),
                    'pet_name' => trim((string) ($adoption['nombreAnimal'] ?? 'Mascota sin nombre')) ?: 'Mascota sin nombre',
                    'pet_type' => trim((string) ($adoption['tipoAnimal'] ?? 'No especificado')) ?: 'No especificado',
                    'pet_type_value' => strtolower(trim((string) ($adoption['tipoAnimal'] ?? ''))),
                    'sex_label' => $this->formatSex((string) ($adoption['sexo'] ?? '')),
                    'breed' => trim((string) ($adoption['raza'] ?? 'No especificada')) ?: 'No especificada',
                    'publisher_name' => $publisher?->name ?? 'Usuario no disponible',
                    'publisher_email' => $publisher?->email ?? 'Sin correo',
                    'publisher_role' => $publisherRole,
                    'publisher_role_label' => $this->formatRoleLabel($publisherRole),
                    'publisher_allowed' => in_array($publisherRole, ['veterinario', 'refugio'], true),
                    'publisher_deleted' => $publisher ? $publisher->trashed() : false,
                    'created_at' => $this->formatDateLabel($createdAt),
                    'created_at_raw' => $createdAt,
                    'is_hidden' => $this->isHidden($adoption),
                    'hidden_at' => $this->formatDateLabel((string) ($adoption['hiddenAt'] ?? '')),
                ];
            })
            ->filter(function (array $adoption): bool {
                return $adoption['publisher_allowed'];
            })
            ->sortByDesc(function (array $adoption): string {
                return $adoption['created_at_raw'];
            })
            ->values();
    }

    protected function isHidden(array $adoption): bool
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

    protected function formatDateLabel(string $value): string
    {
        if ($value === '') {
            return 'Sin fecha';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return $value;
        }
    }

    protected function formatSex(string $sex): string
    {
        $normalized = strtolower(trim($sex));

        if ($normalized === 'hembra') {
            return 'Hembra';
        }

        if ($normalized === 'macho') {
            return 'Macho';
        }

        return 'No especificado';
    }

    protected function formatRoleLabel(string $role): string
    {
        $normalized = strtolower(trim($role));

        return match ($normalized) {
            'admin' => 'Admin',
            'veterinario' => 'Veterinaria',
            'refugio' => 'Refugio',
            'ciudadano' => 'Ciudadano',
            'sin rol' => 'Sin rol',
            default => ucfirst($normalized),
        };
    }
}