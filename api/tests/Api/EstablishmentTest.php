<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\EstablishmentFactory;
use App\DataFixtures\Factory\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class EstablishmentTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    #[Test]
    public function asAnonymousICanGetACollectionOfEstablishments(): void
    {
        EstablishmentFactory::createMany(5);

        $response = $this->client->request('GET', '/establishments');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'totalItems' => 5,
        ]);
        self::assertCount(5, $response->toArray()['member']);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/Establishment/collection.json'));
    }

    #[Test]
    public function asAnonymousICanGetAnEstablishment(): void
    {
        $establishment = EstablishmentFactory::createOne();

        $response = $this->client->request('GET', '/establishments/' . $establishment->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@id' => '/establishments/' . $establishment->getId(),
            'name' => $establishment->name,
        ]);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/Establishment/item.json'));
    }


    #[Test]
    public function asAdminICanDeleteAnEstablishment(): void
    {
        $establishment = EstablishmentFactory::createOne();
        $admin = UserFactory::createOneAdmin();

        $this->client->loginUser($admin->object());
        $this->client->request('DELETE', '/admin/establishments/' . $establishment->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
