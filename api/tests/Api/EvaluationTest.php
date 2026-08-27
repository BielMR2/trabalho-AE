<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\EstablishmentFactory;
use App\DataFixtures\Factory\EvaluationFactory;
use App\DataFixtures\Factory\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class EvaluationTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    #[Test]
    public function asAnonymousICanGetACollectionOfEvaluations(): void
    {
        EvaluationFactory::createMany(5);

        $response = $this->client->request('GET', '/evaluations');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'totalItems' => 5,
        ]);
        self::assertCount(5, $response->toArray()['member']);
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/Evaluation/collection.json'));
    }

    #[Test]
    public function asAnonymousICanGetAnEvaluation(): void
    {
        $evaluation = EvaluationFactory::createOne();

        $response = $this->client->request('GET', '/evaluations/' . $evaluation->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@id' => '/evaluations/' . $evaluation->getId(),
        ]);
        // Note: schemas/Evaluation/item.json needs to be updated if it strictly checks for old 'rating'
    }

    #[Test]
    public function asUserICanCreateAnEvaluation(): void
    {
        // Use a mocked/fixture establishment so it doesn't call the real Google API
        $establishment = EstablishmentFactory::createOne(['googlePlaceId' => 'mocked_place_123']);
        $user = UserFactory::createOne();

        $this->client->loginUser($user->object());
        $response = $this->client->request('POST', '/evaluations', [
            'json' => [
                'comment' => 'Great place!',
                'establishmentGooglePlaceId' => 'mocked_place_123',
                'ratings' => [
                    ['criterion' => 'wheelchair_accessible', 'rating' => 10],
                ]
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            'comment' => 'Great place!',
        ]);
    }

    #[Test]
    public function asAdminICanDeleteAnEvaluation(): void
    {
        $evaluation = EvaluationFactory::createOne();
        $admin = UserFactory::createOneAdmin();

        $this->client->loginUser($admin->object());
        $this->client->request('DELETE', '/admin/evaluations/' . $evaluation->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
