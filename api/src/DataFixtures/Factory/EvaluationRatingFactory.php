<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\EvaluationRating;
use App\Enum\CriterionEnum;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use function Zenstruck\Foundry\lazy;

/**
 * @extends PersistentObjectFactory<EvaluationRating>
 */
final class EvaluationRatingFactory extends PersistentObjectFactory
{
    protected function defaults(): array
    {
        return [
            'rating' => self::faker()->numberBetween(0, 10),
            'criterion' => self::faker()->randomElement(CriterionEnum::cases()),
            'evaluation' => lazy(static fn(): EvaluationFactory => EvaluationFactory::new()),
        ];
    }

    public static function class(): string
    {
        return EvaluationRating::class;
    }
}
