<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\EstablishmentFactory;
use App\DataFixtures\Factory\EvaluationFactory;
use PHPUnit\Framework\Attributes\Test;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class EstablishmentViewportTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    private Client $client;

    // São Paulo center area
    private const SP_CENTER_LAT = -23.55;
    private const SP_CENTER_LNG = -46.63;

    // Viewport covering São Paulo center
    private const SP_VIEWPORT = [
        'south' => -23.60,
        'west' => -46.70,
        'north' => -23.50,
        'east' => -46.56,
    ];

    // Viewport far away (Rio de Janeiro area)
    private const RJ_VIEWPORT = [
        'south' => -23.05,
        'west' => -43.30,
        'north' => -22.85,
        'east' => -43.10,
    ];

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    #[Test]
    public function withoutViewportParamsReturnsAllEstablishments(): void
    {
        EstablishmentFactory::createMany(3);

        $response = $this->client->request('GET', '/establishments');

        self::assertResponseIsSuccessful();
        self::assertCount(3, $response->toArray()['member']);
    }

    #[Test]
    public function viewportWithHighZoomReturnsEstablishmentsInsideBbox(): void
    {
        // Inside SP viewport
        EstablishmentFactory::createOne([
            'name' => 'Inside SP',
            'location' => sprintf('SRID=4326;POINT(%f %f)', self::SP_CENTER_LNG, self::SP_CENTER_LAT),
        ]);

        // Outside SP viewport (Rio de Janeiro)
        EstablishmentFactory::createOne([
            'name' => 'Outside RJ',
            'location' => 'SRID=4326;POINT(-43.200000 -22.950000)',
        ]);

        $response = $this->client->request('GET', '/establishments', [
            'query' => array_merge(
                $this->viewportQuery(self::SP_VIEWPORT),
                ['viewport[zoom]' => 17],
            ),
        ]);

        self::assertResponseIsSuccessful();
        $members = $response->toArray()['member'];
        self::assertCount(1, $members);
        self::assertSame('Inside SP', $members[0]['name']);
    }

    #[Test]
    public function zoomBelowMinimumReturnsEmptyResults(): void
    {
        EstablishmentFactory::createMany(5);

        $response = $this->client->request('GET', '/establishments', [
            'query' => array_merge(
                $this->viewportQuery(self::SP_VIEWPORT),
                ['viewport[zoom]' => 10],
            ),
        ]);

        self::assertResponseIsSuccessful();
        self::assertCount(0, $response->toArray()['member']);
    }

    #[Test]
    public function zoomTierFiltersbyEvaluationCount(): void
    {
        // Establishment with 15 evaluations (should appear at zoom 13, min 10)
        $popular = EstablishmentFactory::createOne([
            'name' => 'Popular Place',
            'location' => sprintf('SRID=4326;POINT(%f %f)', self::SP_CENTER_LNG, self::SP_CENTER_LAT),
        ]);
        EvaluationFactory::createMany(15, ['establishment' => $popular]);

        // Establishment with 2 evaluations (should NOT appear at zoom 13, needs zoom 17+)
        $quiet = EstablishmentFactory::createOne([
            'name' => 'Quiet Place',
            'location' => sprintf('SRID=4326;POINT(%f %f)', self::SP_CENTER_LNG + 0.01, self::SP_CENTER_LAT),
        ]);
        EvaluationFactory::createMany(2, ['establishment' => $quiet]);

        // At zoom 13: only popular (>= 10 evaluations)
        $response = $this->client->request('GET', '/establishments', [
            'query' => array_merge(
                $this->viewportQuery(self::SP_VIEWPORT),
                ['viewport[zoom]' => 13],
            ),
        ]);

        self::assertResponseIsSuccessful();
        $members = $response->toArray()['member'];
        self::assertCount(1, $members);
        self::assertSame('Popular Place', $members[0]['name']);
    }

    #[Test]
    public function highZoomShowsAllEstablishments(): void
    {
        $est1 = EstablishmentFactory::createOne([
            'name' => 'Place A',
            'location' => sprintf('SRID=4326;POINT(%f %f)', self::SP_CENTER_LNG, self::SP_CENTER_LAT),
        ]);

        // No evaluations — should still appear at zoom 17+
        $est2 = EstablishmentFactory::createOne([
            'name' => 'Place B',
            'location' => sprintf('SRID=4326;POINT(%f %f)', self::SP_CENTER_LNG + 0.01, self::SP_CENTER_LAT),
        ]);

        $response = $this->client->request('GET', '/establishments', [
            'query' => array_merge(
                $this->viewportQuery(self::SP_VIEWPORT),
                ['viewport[zoom]' => 17],
            ),
        ]);

        self::assertResponseIsSuccessful();
        self::assertCount(2, $response->toArray()['member']);
    }

    #[Test]
    public function resultsOrderedByEvaluationCountDesc(): void
    {
        $less = EstablishmentFactory::createOne([
            'name' => 'Less Popular',
            'location' => sprintf('SRID=4326;POINT(%f %f)', self::SP_CENTER_LNG, self::SP_CENTER_LAT),
        ]);
        EvaluationFactory::createMany(2, ['establishment' => $less]);

        $more = EstablishmentFactory::createOne([
            'name' => 'More Popular',
            'location' => sprintf('SRID=4326;POINT(%f %f)', self::SP_CENTER_LNG + 0.01, self::SP_CENTER_LAT),
        ]);
        EvaluationFactory::createMany(10, ['establishment' => $more]);

        $response = $this->client->request('GET', '/establishments', [
            'query' => array_merge(
                $this->viewportQuery(self::SP_VIEWPORT),
                ['viewport[zoom]' => 17],
            ),
        ]);

        self::assertResponseIsSuccessful();
        $members = $response->toArray()['member'];
        self::assertCount(2, $members);
        self::assertSame('More Popular', $members[0]['name']);
        self::assertSame('Less Popular', $members[1]['name']);
    }

    #[Test]
    public function incompleteViewportParamsIgnoresFilter(): void
    {
        EstablishmentFactory::createMany(3);

        // Missing 'east' param — should return all (legacy behavior)
        $response = $this->client->request('GET', '/establishments', [
            'query' => [
                'viewport[south]' => -23.60,
                'viewport[west]' => -46.70,
                'viewport[north]' => -23.50,
                'viewport[zoom]' => 15,
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertCount(3, $response->toArray()['member']);
    }

    /**
     * @param array{south: float, west: float, north: float, east: float} $viewport
     * @return array<string, float>
     */
    private function viewportQuery(array $viewport): array
    {
        return [
            'viewport[south]' => $viewport['south'],
            'viewport[west]' => $viewport['west'],
            'viewport[north]' => $viewport['north'],
            'viewport[east]' => $viewport['east'],
        ];
    }
}

