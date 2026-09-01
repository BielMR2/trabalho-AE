<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\EvaluationVote;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use function Zenstruck\Foundry\lazy;

/**
 * @extends PersistentObjectFactory<EvaluationVote>
 */
final class EvaluationVoteFactory extends PersistentObjectFactory
{
    protected function defaults(): array
    {
        return [
            'value' => self::faker()->randomElement([1, -1]),
            'evaluationRating' => lazy(static fn(): EvaluationRatingFactory => EvaluationRatingFactory::new()),
            'user' => lazy(static fn(): UserFactory => UserFactory::new()),
        ];
    }

    public static function class(): string
    {
        return EvaluationVote::class;
    }
}

