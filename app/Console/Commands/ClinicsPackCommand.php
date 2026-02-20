<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ClinicsPackCommand extends Command
{
    protected $signature = 'clinics:pack
        {action : export|import}
        {file : Ruta del archivo JSON}
        {--truncate : Limpia tablas objetivo antes de importar}';

    protected $description = 'Exporta/importa clínicas, servicios y pivotes para sincronizar datos entre equipos';

    public function handle(): int
    {
        $action = strtolower((string) $this->argument('action'));
        $file = $this->resolvePath((string) $this->argument('file'));

        if (! in_array($action, ['export', 'import'], true)) {
            $this->error('Acción inválida. Usa: export o import');
            return self::FAILURE;
        }

        return $action === 'export'
            ? $this->exportPack($file)
            : $this->importPack($file);
    }

    private function exportPack(string $file): int
    {
        $clinics = DB::table('clinics')->get()->map(fn ($r) => (array) $r)->values();
        $clinicIds = $clinics->pluck('id')->filter()->values()->all();
        $userIds = $clinics->pluck('user_id')->filter()->values()->all();

        $pivots = DB::table('clinic_medical_service')
            ->whereIn('clinic_id', $clinicIds ?: [-1])
            ->get()
            ->map(fn ($r) => (array) $r)
            ->values();

        $serviceIds = $pivots->pluck('medical_service_id')->filter()->unique()->values()->all();

        $services = DB::table('medical_services')
            ->whereIn('id', $serviceIds ?: [-1])
            ->get()
            ->map(fn ($r) => (array) $r)
            ->values();

        $roles = DB::table('roles')
            ->where('name', 'veterinario')
            ->get()
            ->map(fn ($r) => (array) $r)
            ->values();

        $roleIds = $roles->pluck('id')->all();

        $modelHasRoles = DB::table('model_has_roles')
            ->whereIn('role_id', $roleIds ?: [-1])
            ->where('model_type', 'App\\Models\\User')
            ->whereIn('model_id', $userIds ?: [-1])
            ->get()
            ->map(fn ($r) => (array) $r)
            ->values();

        $users = DB::table('users')
            ->whereIn('id', $userIds ?: [-1])
            ->get()
            ->map(fn ($r) => (array) $r)
            ->values();

        $payload = [
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'source_db' => config('database.connections.mysql.database'),
                'app' => config('app.name'),
                'version' => 1,
            ],
            'roles' => $roles,
            'users' => $users,
            'model_has_roles' => $modelHasRoles,
            'medical_services' => $services,
            'clinics' => $clinics,
            'clinic_medical_service' => $pivots,
        ];

        File::ensureDirectoryExists(dirname($file));
        File::put($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Pack exportado: {$file}");
        $this->line('Resumen: '.json_encode([
            'roles' => count($payload['roles']),
            'users' => count($payload['users']),
            'model_has_roles' => count($payload['model_has_roles']),
            'medical_services' => count($payload['medical_services']),
            'clinics' => count($payload['clinics']),
            'clinic_medical_service' => count($payload['clinic_medical_service']),
        ], JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }

    private function importPack(string $file): int
    {
        if (! File::exists($file)) {
            $this->error("No existe el archivo: {$file}");
            return self::FAILURE;
        }

        $json = json_decode((string) File::get($file), true);
        if (! is_array($json)) {
            $this->error('JSON inválido');
            return self::FAILURE;
        }

        $roles = collect($json['roles'] ?? [])->map(fn ($r) => $this->filterByColumns('roles', (array) $r))->values();
        $users = collect($json['users'] ?? [])->map(fn ($r) => $this->filterByColumns('users', (array) $r))->values();
        $modelHasRoles = collect($json['model_has_roles'] ?? [])->map(fn ($r) => $this->filterByColumns('model_has_roles', (array) $r))->values();
        $services = collect($json['medical_services'] ?? [])->map(fn ($r) => $this->filterByColumns('medical_services', (array) $r))->values();
        $clinics = collect($json['clinics'] ?? [])->map(fn ($r) => $this->filterByColumns('clinics', (array) $r))->values();
        $pivots = collect($json['clinic_medical_service'] ?? [])->map(fn ($r) => $this->filterByColumns('clinic_medical_service', (array) $r))->values();

        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            if ((bool) $this->option('truncate')) {
                DB::table('clinic_medical_service')->truncate();
                DB::table('clinics')->truncate();
                DB::table('medical_services')->truncate();
                DB::table('model_has_roles')->truncate();
            }

            foreach ($roles as $row) {
                if (! empty($row['id'])) {
                    DB::table('roles')->upsert([$row], ['id'], array_keys($row));
                }
            }

            foreach ($users as $row) {
                if (! empty($row['id'])) {
                    DB::table('users')->upsert([$row], ['id'], array_keys($row));
                }
            }

            foreach ($services as $row) {
                if (! empty($row['id'])) {
                    DB::table('medical_services')->upsert([$row], ['id'], array_keys($row));
                }
            }

            foreach ($clinics as $row) {
                if (! empty($row['id'])) {
                    DB::table('clinics')->upsert([$row], ['id'], array_keys($row));
                }
            }

            foreach ($pivots as $row) {
                if (! empty($row['clinic_id']) && ! empty($row['medical_service_id'])) {
                    DB::table('clinic_medical_service')->upsert(
                        [$row],
                        ['clinic_id', 'medical_service_id'],
                        array_keys($row)
                    );
                }
            }

            foreach ($modelHasRoles as $row) {
                if (! empty($row['role_id']) && ! empty($row['model_id']) && ! empty($row['model_type'])) {
                    DB::table('model_has_roles')->insertOrIgnore($row);
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->error('Error importando pack: '.$e->getMessage());
            return self::FAILURE;
        }

        $this->info("Pack importado: {$file}");
        $this->line('Totales actuales: '.json_encode([
            'clinics' => DB::table('clinics')->count(),
            'medical_services' => DB::table('medical_services')->count(),
            'clinic_medical_service' => DB::table('clinic_medical_service')->count(),
        ], JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }

    private function filterByColumns(string $table, array $row): array
    {
        $columns = Schema::getColumnListing($table);
        return collect($row)
            ->only($columns)
            ->all();
    }

    private function resolvePath(string $path): string
    {
        if (preg_match('/^(?:[A-Za-z]:[\\\\\/]|\/)/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }
}
