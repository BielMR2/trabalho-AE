<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

final class CriterionAverageFilter extends AbstractFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if ($property !== 'criterion_average') {
            return;
        }

        if (!is_array($value)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        foreach ($value as $criterion => $status) {
            $status = strtolower((string)$status);
            $min = null;
            $max = null;

            if ($status === 'ruim') {
                $max = 5.0; // < 5
            } elseif ($status === 'medio') {
                $min = 5.0; // >= 5
                $max = 7.0; // < 7
            } elseif ($status === 'bom') {
                $min = 7.0; // >= 7
            } else {
                continue; 
            }

            $subQueryBuilder = $queryBuilder->getEntityManager()->createQueryBuilder();
            
            $subAlias = $queryNameGenerator->generateParameterName('e');
            $ratingAlias = $queryNameGenerator->generateParameterName('er');
            
            $critParam = $queryNameGenerator->generateParameterName('criterion');
            $minParam = $queryNameGenerator->generateParameterName('min');
            $maxParam = $queryNameGenerator->generateParameterName('max');
            
            $subQueryBuilder->select(sprintf('IDENTITY(%s.establishment)', $subAlias))
                ->from(\App\Entity\Evaluation::class, $subAlias)
                ->join(sprintf('%s.ratings', $subAlias), $ratingAlias)
                ->where(sprintf('%s.criterion = :%s', $ratingAlias, $critParam))
                ->groupBy(sprintf('%s.establishment', $subAlias));

            if ($min !== null) {
                $subQueryBuilder->andHaving(sprintf('AVG(%s.rating) >= :%s', $ratingAlias, $minParam));
                $queryBuilder->setParameter($minParam, $min);
            }
            if ($max !== null) {
                $subQueryBuilder->andHaving(sprintf('AVG(%s.rating) < :%s', $ratingAlias, $maxParam));
                $queryBuilder->setParameter($maxParam, $max);
            }

            $queryBuilder->andWhere($queryBuilder->expr()->in(sprintf('%s.id', $alias), $subQueryBuilder->getDQL()));
            $queryBuilder->setParameter($critParam, $criterion);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'criterion_average' => [
                'property' => 'criterion_average',
                'type' => 'array',
                'required' => false,
                'description' => 'Filter by criterion average status (bom, medio, ruim). Example: ?criterion_average[wheelchair_accessible]=bom',
            ]
        ];
    }
}
