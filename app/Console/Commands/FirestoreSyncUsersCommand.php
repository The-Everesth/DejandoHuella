<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\Firestore\UsersFirestoreService;

class FirestoreSyncUsersCommand extends Command
{
    protected $signature = 'firestore:sync-users {--limit=}';
    protected $description = 'Sincroniza todos los usuarios de MySQL a Firestore de forma idempotente';

    public function handle(UsersFirestoreService $usersService)
    {
        $this->info('Iniciando sincronización de usuarios...');

        $query = User::query();
        
        // Límite opcional
        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $users = $query->get();
        $total = $users->count();
        
        if ($total === 0) {
            $this->warn('No hay usuarios para sincronizar.');
            return 0;
        }

        $created = 0;
        $updated = 0;
        $failed = 0;

        $this->output->progressStart($total);

        foreach ($users as $user) {
            $result = $usersService->syncFromUser($user);

            if ($result['success']) {
                
                $updated++;
            } else {
                $failed++;
                $this->line("");
                $this->error("Error en usuario {$user->id}: {$result['error']}");
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->newLine(2);
        $this->info("Sincronización completa:");
        $this->line("  Total procesados: {$total}");
        $this->line("  Actualizados: {$updated}");
        $this->line("  Errores: {$failed}");

        return 0;
    }
}
