<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\EstablishmentFactory;
use App\DataFixtures\Factory\EvaluationFactory;
use App\DataFixtures\Factory\EvaluationRatingFactory;
use App\DataFixtures\Factory\EvaluationVoteFactory;
use App\DataFixtures\Factory\FileFactory;
use App\DataFixtures\Factory\ImageFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Enum\CriterionEnum;
use Zenstruck\Foundry\Story;

final class DefaultStory extends Story
{
    public function build(): void
    {
        $criteria = CriterionEnum::cases();

        // Create random files and images first so we can attach images to ratings
        FileFactory::createMany(10);
        $images = ImageFactory::createMany(10);

        // Create establishments
        $establishments = EstablishmentFactory::createMany(30);

        // Create users for voting
        $voters = UserFactory::createMany(5);

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
                        // Select 0 to 2 random images for this rating
                        $ratingImages = [];
                        $numImages = random_int(0, 2);
                        if ($numImages > 0) {
                            $shuffledImages = $images;
                            shuffle($shuffledImages);
                            $ratingImages = array_slice($shuffledImages, 0, $numImages);
                        }

                        $rating = EvaluationRatingFactory::createOne([
                            'evaluation' => $evaluation,
                            'criterion' => $shuffledCriteria[$i],
                            'images' => $ratingImages,
                        ]);

                        // Add random votes to some ratings (50% chance per voter)
                        foreach ($voters as $voter) {
                            if (random_int(0, 1) === 1) {
                                EvaluationVoteFactory::createOne([
                                    'evaluationRating' => $rating,
                                    'user' => $voter,
                                ]);
                            }
                        }
                    }
                }
            }
        }

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
