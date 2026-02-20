<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationsAuditCommand extends Command
{
    protected $signature = 'migrations:audit-existing-creates {--stamp : Inserta en tabla migrations las create pendientes cuyo table ya existe} {--batch= : Batch a usar al hacer stamp (default: max batch actual o 1)}';

    protected $description = 'Audita migraciones create_*_table pendientes y detecta tablas ya existentes';

    public function handle(): int
    {
        $files = collect(glob(database_path('migrations/*.php')))
            ->map(fn (string $file): string => basename($file, '.php'))
            ->sort()
            ->values();

        $ran = DB::table('migrations')->pluck('migration')->all();
        $ranLookup = array_flip($ran);

        $conflicts = $files
            ->filter(fn (string $migration): bool => !isset($ranLookup[$migration]))
            ->filter(fn (string $migration): bool => preg_match('/create_(.+)_table$/', $migration) === 1)
            ->map(function (string $migration): array {
                preg_match('/create_(.+)_table$/', $migration, $matches);
                $table = $matches[1] ?? '';

                return [
                    'migration' => $migration,
                    'table' => $table,
                    'exists' => Schema::hasTable($table),
                ];
            })
            ->filter(fn (array $row): bool => $row['exists'] === true)
            ->values();

        if ($conflicts->isEmpty()) {
            $this->info('No hay conflictos: ninguna create pendiente apunta a una tabla ya existente.');
            return self::SUCCESS;
        }

        $this->warn('Conflictos detectados (create pendiente + tabla existente):');
        $this->table(['Migration', 'Table'], $conflicts->map(fn (array $row): array => [$row['migration'], $row['table']])->all());

        if (!$this->option('stamp')) {
            $this->line('Modo auditoría: no se realizaron cambios. Usa --stamp para registrar estas migraciones en la tabla migrations.');
            return self::SUCCESS;
        }

        $batchOption = $this->option('batch');
        $maxBatch = (int) DB::table('migrations')->max('batch');
        $batch = $batchOption !== null ? (int) $batchOption : max($maxBatch, 1);

        $inserted = 0;
        foreach ($conflicts as $row) {
            $ok = DB::table('migrations')->insertOrIgnore([
                'migration' => $row['migration'],
                'batch' => $batch,
            ]);

            if ($ok === 1) {
                $inserted++;
            }
        }

        $this->info("Stamp completado. Registros insertados: {$inserted}. Batch usado: {$batch}.");

        return self::SUCCESS;
    }
}
