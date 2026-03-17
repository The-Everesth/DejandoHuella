<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Firestore\AdoptionRequestsFirestoreService;
use App\Services\Firestore\AdoptionsFirestoreService;
use App\Services\Firestore\FirestoreUserRoleService;
use Tests\TestCase;

class MyRequestedAdoptionsApiTest extends TestCase
{
    public function test_ciudadano_can_get_requested_adoption_ids(): void
    {
        $user = User::factory()->make();
        $user->id = 401;

        $this->bindServices(
            [
                (int) $user->id => [
                    ['adoptionId' => 'ad_100', 'status' => 'pendiente'],
                    ['adoptionId' => 'ad_100', 'status' => 'aprobada'],
                    ['adoptionId' => 'ad_200', 'status' => 'rechazada'],
                    ['adoptionId' => 'ad_300', 'status' => 'cancelada'],
                    ['adoptionId' => ''],
                ],
            ],
            [
                (int) $user->id => ['ciudadano'],
            ]
        );

        $response = $this
            ->actingAs($user)
            ->getJson(route('my.requested.adoptions'));

        $response->assertOk()->assertJsonPath('success', true);

        $ids = $response->json('data');
        $this->assertIsArray($ids);
        $this->assertEqualsCanonicalizing(['ad_100', 'ad_200'], $ids);
        $this->assertNotContains('ad_300', $ids);
    }

    public function test_non_ciudadano_cannot_get_requested_adoption_ids(): void
    {
        $user = User::factory()->make();
        $user->id = 402;

        $this->bindServices(
            [
                (int) $user->id => [
                    ['adoptionId' => 'ad_100'],
                ],
            ],
            [
                (int) $user->id => ['veterinario'],
            ]
        );

        $response = $this
            ->actingAs($user)
            ->getJson(route('my.requested.adoptions'));

        $response->assertForbidden();
    }

    private function bindServices(array $requestsByApplicantId, array $rolesByUserId): void
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

        $this->app->instance(AdoptionsFirestoreService::class, new class extends AdoptionsFirestoreService {
            public function __construct()
            {
            }
        });

        $this->app->instance(AdoptionRequestsFirestoreService::class, new class($requestsByApplicantId) extends AdoptionRequestsFirestoreService {
            public function __construct(private array $requestsByApplicantId)
            {
            }

            public function listByApplicant(int $applicantId): array
            {
                return $this->requestsByApplicantId[$applicantId] ?? [];
            }
        });
    }
}
