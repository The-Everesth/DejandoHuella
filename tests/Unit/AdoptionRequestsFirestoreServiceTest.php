<?php

namespace Tests\Unit;

use App\Services\Firestore\AdoptionRequestsFirestoreService;
use App\Services\Firestore\FirestoreRestClient;
use Mockery;
use PHPUnit\Framework\TestCase;

class AdoptionRequestsFirestoreServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_for_adoption_allows_recreate_when_previous_request_is_cancelled(): void
    {
        $requestId = 'req_adopcion-1_u_7';

        $client = Mockery::mock(FirestoreRestClient::class);
        $client
            ->shouldReceive('getDoc')
            ->with('solicitudes_adopcion', $requestId)
            ->andReturn(
                ['id' => $requestId, 'status' => 'cancelada'],
                [
                    'id' => $requestId,
                    'adoptionId' => 'adopcion-1',
                    'applicantId' => 7,
                    'status' => 'pendiente',
                ]
            );

        $client
            ->shouldReceive('patchDoc')
            ->once()
            ->withArgs(function (string $collection, string $id, array $data) use ($requestId): bool {
                $this->assertSame('solicitudes_adopcion', $collection);
                $this->assertSame($requestId, $id);
                $this->assertSame('pendiente', $data['status'] ?? null);
                $this->assertSame('adopcion-1', $data['adoptionId'] ?? null);
                $this->assertSame(7, $data['applicantId'] ?? null);
                $this->assertArrayHasKey('createdAt', $data);
                $this->assertArrayHasKey('updatedAt', $data);
                return true;
            })
            ->andReturn(true);

        $service = new AdoptionRequestsFirestoreService($client);

        $result = $service->createForAdoption('adopcion-1', 7, [
            'petName' => 'Tomas',
        ]);

        $this->assertSame('pendiente', $result['status']);
        $this->assertSame('adopcion-1', $result['adoptionId']);
    }

    public function test_create_for_adoption_throws_when_existing_request_is_not_cancelled(): void
    {
        $requestId = 'req_adopcion-2_u_7';

        $client = Mockery::mock(FirestoreRestClient::class);
        $client
            ->shouldReceive('getDoc')
            ->once()
            ->with('solicitudes_adopcion', $requestId)
            ->andReturn(['id' => $requestId, 'status' => 'pendiente']);

        $client->shouldNotReceive('patchDoc');
        $client->shouldNotReceive('createDocument');

        $service = new AdoptionRequestsFirestoreService($client);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Ya enviaste una solicitud para esta mascota.');

        $service->createForAdoption('adopcion-2', 7, [
            'petName' => 'Luna',
        ]);
    }
}
