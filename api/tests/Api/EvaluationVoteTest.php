<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\EvaluationFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\EvaluationVote;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class EvaluationVoteTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    #[Test]
    public function asAnonymousICannotVote(): void
    {
        $evaluation = EvaluationFactory::createOne();

        $this->client->request('POST', '/evaluation_votes', [
            'json' => [
                'evaluation' => '/evaluations/' . $evaluation->getId(),
                'value' => 1
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    public function asUserICanVote(): void
    {
        $user = UserFactory::createOne();
        $evaluation = EvaluationFactory::createOne();

        $this->client->loginUser($user->object());
        $response = $this->client->request('POST', '/evaluation_votes', [
            'json' => [
                'evaluation' => '/evaluations/' . $evaluation->getId(),
                'value' => 1
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertJsonContains([
            'value' => 1,
        ]);
        
        // Check netVotes
        $this->client->request('GET', '/evaluations/' . $evaluation->getId());
        self::assertJsonContains(['netVotes' => 1]);
    }

    #[Test]
    public function asUserICanChangeMyVote(): void
    {
        $user = UserFactory::createOne();
        $evaluation = EvaluationFactory::createOne();

        $this->client->loginUser($user->object());
        
        // Initial upvote
        $this->client->request('POST', '/evaluation_votes', [
            'json' => [
                'evaluation' => '/evaluations/' . $evaluation->getId(),
                'value' => 1
            ],
        ]);
        
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        // Change to downvote
        $this->client->request('POST', '/evaluation_votes', [
            'json' => [
                'evaluation' => '/evaluations/' . $evaluation->getId(),
                'value' => -1
            ],
        ]);
        
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertJsonContains([
            'value' => -1,
        ]);
        
        // Check netVotes
        $this->client->request('GET', '/evaluations/' . $evaluation->getId());
        self::assertJsonContains(['netVotes' => -1]);
    }
}
