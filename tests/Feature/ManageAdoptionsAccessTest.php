<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Firestore\AdoptionRequestsFirestoreService;
use App\Services\Firestore\AdoptionsFirestoreService;
use App\Services\Firestore\FirestoreUserRoleService;
use Tests\TestCase;

class ManageAdoptionsAccessTest extends TestCase
{
    public function test_refugio_can_access_the_manage_adoptions_page(): void
    {
        $user = User::factory()->make();
        $user->id = 101;

        $this->bindRoleService([
            (int) $user->id => ['refugio'],
        ]);
        $this->bindAdoptionsServices([]);

        $response = $this
            ->actingAs($user)
            ->get(route('vet.my.adoptions'));

        $response
            ->assertOk()
            ->assertSee('Mis adopciones');
    }

    public function test_ciudadano_cannot_access_the_manage_adoptions_page(): void
    {
        $user = User::factory()->make();
        $user->id = 202;

        $this->bindRoleService([
            (int) $user->id => ['ciudadano'],
        ]);
        $this->bindAdoptionsServices([]);

        $response = $this
            ->actingAs($user)
            ->get(route('vet.my.adoptions'));

        $response->assertForbidden();
    }

    private function bindRoleService(array $rolesByUserId): void
    {
        $normalizedRolesByUserId = [];
        foreach ($rolesByUserId as $userId => $roles) {
            $normalizedRolesByUserId[(int) $userId] = array_values(array_filter(array_map(
                static fn ($role): string => strtolower(trim((string) $role)),
                (array) $roles
            )));
        }

        $this->app->instance(FirestoreUserRoleService::class, new class($normalizedRolesByUserId) extends FirestoreUserRoleService {
            public function __construct(private array $rolesByUserId)
            {
            }

            public function getRolesByLaravelUserId(int $laravelUserId): array
            {
                return $this->rolesByUserId[$laravelUserId] ?? [];
            }

            public function hasRoleByLaravelUserId(int $laravelUserId, $requestedRoles): bool
            {
                $userRoles = $this->getRolesByLaravelUserId($laravelUserId);
                $requested = [];

                if (is_string($requestedRoles)) {
                    $requested = preg_split('/[|,]/', $requestedRoles) ?: [];
                } elseif (is_array($requestedRoles)) {
                    array_walk_recursive($requestedRoles, static function ($role) use (&$requested): void {
                        $requested[] = $role;
                    });
                } else {
                    $requested = [$requestedRoles];
                }

                $normalizedRequested = array_values(array_filter(array_map(
                    static fn ($role): string => strtolower(trim((string) $role)),
                    $requested
                )));

                return ! empty(array_intersect($userRoles, $normalizedRequested));
            }
        });
    }

    private function bindAdoptionsServices(array $adoptions): void
    {
        $this->app->instance(AdoptionsFirestoreService::class, new class($adoptions) extends AdoptionsFirestoreService {
            public function __construct(private array $adoptions)
            {
            }

            public function list(): array
            {
                return $this->adoptions;
            }
        });

        $this->app->instance(AdoptionRequestsFirestoreService::class, new class extends AdoptionRequestsFirestoreService {
            public function __construct()
            {
            }
        });
    }
}