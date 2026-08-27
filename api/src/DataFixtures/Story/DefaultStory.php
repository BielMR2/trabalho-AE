<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\EstablishmentFactory;
use App\DataFixtures\Factory\EvaluationFactory;
use App\DataFixtures\Factory\FileFactory;
use App\DataFixtures\Factory\ImageFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Enum\CriterionEnum;
use Zenstruck\Foundry\Story;

use App\DataFixtures\Factory\EvaluationRatingFactory;

final class DefaultStory extends Story
{
    public function build(): void
    {
        $criteria = CriterionEnum::cases();

        // Create establishments
        $establishments = EstablishmentFactory::createMany(30);

        // Add random evaluations to the establishments
        foreach ($establishments as $establishment) {
            if (($number = random_int(0, 5)) !== 0) {
                $evaluations = EvaluationFactory::createMany($number, [
                    'establishment' => $establishment,
                ]);

                foreach ($evaluations as $evaluation) {
                    // Create 2 to 4 ratings per evaluation, randomly selecting criteria
                    $numRatings = random_int(2, 4);
                    $shuffledCriteria = $criteria;
                    shuffle($shuffledCriteria);
                    for ($i = 0; $i < $numRatings; $i++) {
                        EvaluationRatingFactory::createOne([
                            'evaluation' => $evaluation,
                            'criterion' => $shuffledCriteria[$i],
                        ]);
                    }
                }
            }
        }

        // Create random files and images
        FileFactory::createMany(10);
        ImageFactory::createMany(10);

        // Create default user
        UserFactory::createOne([
            'email' => 'john.doe@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        // Create admin user
        UserFactory::createOneAdmin();
    }
}
