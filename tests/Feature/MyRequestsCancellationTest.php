<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Firestore\AdoptionRequestsFirestoreService;
use App\Services\Firestore\AdoptionsFirestoreService;
use App\Services\Firestore\FirestoreUserRoleService;
use Tests\TestCase;

class MyRequestsCancellationTest extends TestCase
{
    public function test_ciudadano_can_cancel_own_pending_request(): void
    {
        $user = User::factory()->make();
        $user->id = 300;

        $requestsService = $this->bindServices(
            [
                'req_adopcion_u_300' => [
                    'id' => 'req_adopcion_u_300',
                    'applicantId' => 300,
                    'status' => 'pendiente',
                ],
            ],
            [
                (int) $user->id => ['ciudadano'],
            ]
        );

        $response = $this
            ->actingAs($user)
            ->from(route('my.requests'))
            ->patch(route('my.requests.cancel', ['requestId' => 'req_adopcion_u_300']));

        $response
            ->assertRedirect(route('my.requests'))
            ->assertSessionHas('success', 'Solicitud cancelada correctamente.');

        $this->assertSame('cancelada', $requestsService->requests['req_adopcion_u_300']['status']);
    }

    public function test_ciudadano_cannot_cancel_other_user_request(): void
    {
        $user = User::factory()->make();
        $user->id = 301;

        $this->bindServices(
            [
                'req_adopcion_u_999' => [
                    'id' => 'req_adopcion_u_999',
                    'applicantId' => 999,
                    'status' => 'pendiente',
                ],
            ],
            [
                (int) $user->id => ['ciudadano'],
            ]
        );

        $response = $this
            ->actingAs($user)
            ->patch(route('my.requests.cancel', ['requestId' => 'req_adopcion_u_999']));

        $response->assertForbidden();
    }

    private function bindServices(array $requestsById, array $rolesByUserId)
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

        $requestsService = new class($requestsById) extends AdoptionRequestsFirestoreService {
            public array $requests;

            public function __construct(array $requests)
            {
                $this->requests = $requests;
            }

            public function get(string $requestId): ?array
            {
                return $this->requests[$requestId] ?? null;
            }

            public function setStatus(string $requestId, string $status, array $extraData = []): bool
            {
                if (! isset($this->requests[$requestId])) {
                    return false;
                }

                $this->requests[$requestId]['status'] = $status;
                $this->requests[$requestId] = array_merge($this->requests[$requestId], $extraData);

                return true;
            }
        };

        $this->app->instance(AdoptionRequestsFirestoreService::class, $requestsService);

        return $requestsService;
    }
}
