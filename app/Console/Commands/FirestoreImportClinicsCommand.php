<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Firestore\FirestoreRestClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FirestoreImportClinicsCommand extends Command
{
    protected $signature = 'firestore:import-clinics
        {--ids=* : IDs específicos de Firestore (ej: c_6 c_7 c_8)}
        {--collection=clinicas : Colección de Firestore a importar}
        {--prune : Elimina en MySQL clínicas que ya no existen en Firestore}';

    protected $description = 'Importa clínicas desde Firestore a MySQL (users/roles incluidos)';

    public function handle(FirestoreRestClient $client): int
    {
        $collection = (string) $this->option('collection');
        $ids = collect((array) $this->option('ids'))
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->values();

        $allFromFirestore = collect($client->listDocs($collection));
        $all = $allFromFirestore;

        if ($ids->isNotEmpty()) {
            $lookup = $ids->flip();
            $all = $all->filter(fn (array $row) => $lookup->has((string) ($row['id'] ?? '')))->values();
        }

        if ($all->isEmpty()) {
            $this->warn('No se encontraron clínicas para importar con esos filtros.');
            return self::SUCCESS;
        }

        DB::table('roles')->updateOrInsert(
            ['name' => 'veterinario', 'guard_name' => 'web'],
            ['updated_at' => now(), 'created_at' => now()]
        );

        $imported = 0;
        $skipped = 0;

        foreach ($all as $clinicFs) {
            $userId = (int) ($clinicFs['userId'] ?? 0);
            if ($userId <= 0) {
                $skipped++;
                continue;
            }

            $user = User::find($userId);
            if (! $user) {
                DB::table('users')->updateOrInsert(
                    ['id' => $userId],
                    [
                        'name' => (string) ($clinicFs['name'] ?? ('Vet '.$userId)),
                        'email' => 'vet'.$userId.'@import.local',
                        'password' => Hash::make(Str::random(24)),
                        'email_verified_at' => now(),
                        'remember_token' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'deleted_at' => null,
                    ]
                );
                $user = User::find($userId);
            }

            if ($user && method_exists($user, 'assignRole') && ! $user->hasRole('veterinario')) {
                $user->assignRole('veterinario');
            }

            $firestoreId = (string) ($clinicFs['id'] ?? '');
            $idFromFs = $this->numericIdFromFirestore($firestoreId);

            $payload = [
                'user_id' => $userId,
                'owner_vet_id' => $userId,
                'name' => (string) ($clinicFs['name'] ?? ('Clínica '.$firestoreId)),
                'phone' => $this->nullableString($clinicFs['phone'] ?? null),
                'email' => $this->nullableString($clinicFs['email'] ?? null),
                'address' => $this->nullableString($clinicFs['address'] ?? null),
                'address_line' => $this->nullableString($clinicFs['address'] ?? null),
                'description' => $this->nullableString($clinicFs['description'] ?? null),
                'opening_hours' => $this->nullableString($clinicFs['opening_hours'] ?? null),
                'website' => $this->nullableString($clinicFs['website'] ?? null),
                'is_public' => (bool) ($clinicFs['is_public'] ?? true),
                'city' => 'Durango',
                'state' => 'Durango',
                'created_at' => $this->toMysqlDate($clinicFs['createdAt'] ?? null) ?? now(),
                'updated_at' => $this->toMysqlDate($clinicFs['updatedAt'] ?? null) ?? now(),
            ];

            $payload = $this->filterColumns('clinics', $payload);

            if ($idFromFs !== null) {
                DB::table('clinics')->updateOrInsert(['id' => $idFromFs], $payload);
            } else {
                $existingId = DB::table('clinics')
                    ->where('user_id', $userId)
                    ->where('name', $payload['name'] ?? '')
                    ->value('id');

                if ($existingId) {
                    DB::table('clinics')->where('id', $existingId)->update($payload);
                } else {
                    DB::table('clinics')->insert($payload);
                }
            }

            $imported++;
        }

        $deleted = 0;
        if ((bool) $this->option('prune')) {
            $deleted = $this->pruneDeletedClinics($allFromFirestore, $ids);
        }

        $this->info("Importación completada. Clínicas importadas/actualizadas: {$imported}. Omitidas: {$skipped}. Eliminadas por prune: {$deleted}.");

        return self::SUCCESS;
    }

    private function numericIdFromFirestore(string $id): ?int
    {
        if (preg_match('/^c_(\d+)$/', $id, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = trim((string) $value);
        return $clean === '' ? null : $clean;
    }

    private function toMysqlDate(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private function filterColumns(string $table, array $payload): array
    {
        $columns = collect(Schema::getColumnListing($table))->flip();
        return collect($payload)
            ->filter(fn ($_, $key) => $columns->has($key))
            ->all();
    }

    private function pruneDeletedClinics(\Illuminate\Support\Collection $allFromFirestore, \Illuminate\Support\Collection $requestedIds): int
    {
        $firestoreNumericIds = $allFromFirestore
            ->pluck('id')
            ->map(fn ($id) => $this->numericIdFromFirestore((string) $id))
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($requestedIds->isNotEmpty()) {
            $requestedNumericIds = $requestedIds
                ->map(fn ($id) => $this->numericIdFromFirestore((string) $id))
                ->filter(fn ($id) => $id !== null)
                ->map(fn ($id) => (int) $id)
                ->values();

            $toDelete = $requestedNumericIds
                ->reject(fn (int $id) => $firestoreNumericIds->contains($id))
                ->values();
        } else {
            $localIds = DB::table('clinics')->pluck('id')->map(fn ($id) => (int) $id)->values();
            $toDelete = $localIds
                ->reject(fn (int $id) => $firestoreNumericIds->contains($id))
                ->values();
        }

        if ($toDelete->isEmpty()) {
            return 0;
        }

        return DB::table('clinics')->whereIn('id', $toDelete->all())->delete();
    }
}
