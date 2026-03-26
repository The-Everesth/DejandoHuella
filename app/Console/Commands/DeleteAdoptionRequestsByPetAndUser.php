<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdoptionRequestsService;

class DeleteAdoptionRequestsByPetAndUser extends Command
{
    protected $signature = 'adoptions:delete-by-pet-user {petNames*} {--user=}';
    protected $description = 'Elimina solicitudes de adopción por nombre de mascota y usuario (email o id)';

    public function handle(AdoptionRequestsService $adoptionRequests)
    {
        $petNames = $this->argument('petNames');
        $user = $this->option('user');
        if (!$user) {
            $this->error('Debes especificar el usuario con --user=');
            return 1;
        }

        $found = 0;
        $deleted = 0;
        $all = $adoptionRequests->all();
        foreach ($all as $request) {
            $petName = strtolower(trim($request['petName'] ?? ''));
            $applicant = strtolower(trim(($request['applicantEmail'] ?? $request['applicantId'] ?? '')));
            if (in_array($petName, array_map('strtolower', $petNames)) && str_contains($applicant, strtolower($user))) {
                $found++;
                $adoptionRequests->delete($request['id']);
                $deleted++;
                $this->info("Eliminada solicitud de {$petName} para usuario {$applicant}");
            }
        }
        $this->info("Total encontradas: $found, eliminadas: $deleted");
        return 0;
    }
}
