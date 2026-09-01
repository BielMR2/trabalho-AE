<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\EvaluationFactory;
use App\DataFixtures\Factory\EvaluationRatingFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Entity\EvaluationVote;
use App\Enum\CriterionEnum;
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
        $rating = EvaluationRatingFactory::createOne([
            'evaluation' => $evaluation,
            'criterion' => CriterionEnum::WheelchairAccessible,
        ]);

        $this->client->request('POST', '/evaluation_votes', [
            'json' => [
                'evaluationRating' => '/evaluation_ratings/' . $rating->getId(),
                'value' => 1
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    public function asUserICanVoteOnRating(): void
    {
        $user = UserFactory::createOne();
        $evaluation = EvaluationFactory::createOne();
        $rating = EvaluationRatingFactory::createOne([
            'evaluation' => $evaluation,
            'criterion' => CriterionEnum::WheelchairAccessible,
        ]);

        $this->client->loginUser($user->object());
        $this->client->request('POST', '/evaluation_votes', [
            'json' => [
                'evaluationRating' => '/evaluation_ratings/' . $rating->getId(),
                'value' => 1
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertJsonContains([
            'value' => 1,
        ]);

        // Check netVotes on the rating via evaluation endpoint
        $response = $this->client->request('GET', '/evaluations/' . $evaluation->getId());
        $data = $response->toArray();

        // Find the rating in the response
        $ratingData = $data['ratings'][0] ?? null;
        self::assertNotNull($ratingData);
        self::assertSame(1, $ratingData['netVotes']);
    }

    #[Test]
    public function asUserICanChangeMyVote(): void
    {
        $user = UserFactory::createOne();
        $evaluation = EvaluationFactory::createOne();
        $rating = EvaluationRatingFactory::createOne([
            'evaluation' => $evaluation,
            'criterion' => CriterionEnum::AccessibleRestroom,
        ]);

        $this->client->loginUser($user->object());

        // Initial upvote
        $this->client->request('POST', '/evaluation_votes', [
            'json' => [
                'evaluationRating' => '/evaluation_ratings/' . $rating->getId(),
                'value' => 1
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Change to downvote (upsert)
        $this->client->request('POST', '/evaluation_votes', [
            'json' => [
                'evaluationRating' => '/evaluation_ratings/' . $rating->getId(),
                'value' => -1
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertJsonContains([
            'value' => -1,
        ]);

        // Check netVotes on the rating
        $response = $this->client->request('GET', '/evaluations/' . $evaluation->getId());
        $data = $response->toArray();
        $ratingData = $data['ratings'][0] ?? null;
        self::assertNotNull($ratingData);
        self::assertSame(-1, $ratingData['netVotes']);
    }

    #[Test]
    public function currentUserVoteIsPopulatedWhenAuthenticated(): void
    {
        $user = UserFactory::createOne();
        $evaluation = EvaluationFactory::createOne();
        $rating1 = EvaluationRatingFactory::createOne([
            'evaluation' => $evaluation,
            'criterion' => CriterionEnum::WheelchairAccessible,
        ]);
        $rating2 = EvaluationRatingFactory::createOne([
            'evaluation' => $evaluation,
            'criterion' => CriterionEnum::TactilePaving,
        ]);

        $this->client->loginUser($user->object());

        // Vote only on rating1
        $this->client->request('POST', '/evaluation_votes', [
            'json' => [
                'evaluationRating' => '/evaluation_ratings/' . $rating1->getId(),
                'value' => 1
            ],
        ]);

        // GET evaluation — should show currentUserVote
        $response = $this->client->request('GET', '/evaluations/' . $evaluation->getId());
        $data = $response->toArray();

        // Find ratings by criterion
        $ratings = $data['ratings'];
        self::assertCount(2, $ratings);

        foreach ($ratings as $r) {
            if ($r['criterion'] === '/criterion_enums/wheelchair_accessible') {
                self::assertSame(1, $r['currentUserVote'], 'Voted rating should show currentUserVote = 1');
            } else {
                self::assertNull($r['currentUserVote'], 'Unvoted rating should show currentUserVote = null');
            }
        }
    }

    #[Test]
    public function currentUserVoteIsNullWhenAnonymous(): void
    {
        $evaluation = EvaluationFactory::createOne();
        EvaluationRatingFactory::createOne([
            'evaluation' => $evaluation,
            'criterion' => CriterionEnum::BrailleSignage,
        ]);

        // GET without auth
        $response = $this->client->request('GET', '/evaluations/' . $evaluation->getId());
        $data = $response->toArray();

        foreach ($data['ratings'] as $r) {
            self::assertNull($r['currentUserVote'], 'Anonymous user should see null currentUserVote');
        }
    }
}
