<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Evaluation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ViewportFilter extends AbstractFilter
{
    /** @var array<int, int> */
    private readonly array $zoomTiers;
    private readonly int $minZoom;
    private readonly int $maxResults;

    /**
     * @param array<int, int> $zoomTiers
     */
    public function __construct(
        #[Autowire(param: 'viewport.min_zoom')]
        int $minZoom = 13,
        #[Autowire(param: 'viewport.zoom_tiers')]
        array $zoomTiers = [13 => 10, 15 => 3, 17 => 0],
        #[Autowire(param: 'viewport.max_results')]
        int $maxResults = 100,
        mixed ...$parentArgs,
    ) {
        $this->minZoom = $minZoom;
        $this->maxResults = $maxResults;

        ksort($zoomTiers);
        $this->zoomTiers = $zoomTiers;

        parent::__construct(...$parentArgs);
    }

    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if ($property !== 'viewport') {
            return;
        }

        if (!is_array($value)) {
            return;
        }

        $south = isset($value['south']) ? (float) $value['south'] : null;
        $west = isset($value['west']) ? (float) $value['west'] : null;
        $north = isset($value['north']) ? (float) $value['north'] : null;
        $east = isset($value['east']) ? (float) $value['east'] : null;
        $zoom = isset($value['zoom']) ? (int) $value['zoom'] : null;

        // Without zoom or incomplete bbox, skip filtering (legacy behavior)
        if ($zoom === null || $south === null || $west === null || $north === null || $east === null) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        // Zoom too low — return empty results
        if ($zoom < $this->minZoom) {
            $queryBuilder->andWhere('1 = 0');

            return;
        }

        // Bounding box spatial filter via PostGIS
        $southParam = $queryNameGenerator->generateParameterName('vp_south');
        $westParam = $queryNameGenerator->generateParameterName('vp_west');
        $northParam = $queryNameGenerator->generateParameterName('vp_north');
        $eastParam = $queryNameGenerator->generateParameterName('vp_east');

        $queryBuilder->andWhere(sprintf(
            'ST_Within(%s.location, ST_MakeEnvelope(:%s, :%s, :%s, :%s, 4326)) = true',
            $alias,
            $westParam,
            $southParam,
            $eastParam,
            $northParam,
        ));
        $queryBuilder->setParameter($southParam, $south);
        $queryBuilder->setParameter($westParam, $west);
        $queryBuilder->setParameter($northParam, $north);
        $queryBuilder->setParameter($eastParam, $east);

        // Correlated subquery: count active evaluations per establishment
        $evalAlias = $queryNameGenerator->generateParameterName('vf_eval');
        $evalCountDql = sprintf(
            '(SELECT COUNT(%s.id) FROM %s %s WHERE %s.establishment = %s AND %s.active = true)',
            $evalAlias,
            Evaluation::class,
            $evalAlias,
            $evalAlias,
            $alias,
            $evalAlias,
        );

        // Zoom tier: filter by minimum evaluation count
        $minCount = $this->getMinEvaluationCount($zoom);
        if ($minCount > 0) {
            $minCountParam = $queryNameGenerator->generateParameterName('vp_min_eval');
            $queryBuilder->andWhere(sprintf('%s >= :%s', $evalCountDql, $minCountParam));
            $queryBuilder->setParameter($minCountParam, $minCount);
        }

        // Order by relevance (most evaluations first)
        $queryBuilder->addSelect(sprintf('%s AS HIDDEN vp_eval_count', $evalCountDql));
        $queryBuilder->addOrderBy('vp_eval_count', 'DESC');

        // Cap results per viewport
        $queryBuilder->setMaxResults($this->maxResults);
    }

    private function getMinEvaluationCount(int $zoom): int
    {
        $result = 0;

        foreach ($this->zoomTiers as $tierZoom => $minCount) {
            if ($zoom >= $tierZoom) {
                $result = $minCount;
            }
        }

        return $result;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'viewport[south]' => [
                'property' => 'viewport',
                'type' => 'float',
                'required' => false,
                'description' => 'South latitude of the map viewport bounding box',
            ],
            'viewport[west]' => [
                'property' => 'viewport',
                'type' => 'float',
                'required' => false,
                'description' => 'West longitude of the map viewport bounding box',
            ],
            'viewport[north]' => [
                'property' => 'viewport',
                'type' => 'float',
                'required' => false,
                'description' => 'North latitude of the map viewport bounding box',
            ],
            'viewport[east]' => [
                'property' => 'viewport',
                'type' => 'float',
                'required' => false,
                'description' => 'East longitude of the map viewport bounding box',
            ],
            'viewport[zoom]' => [
                'property' => 'viewport',
                'type' => 'integer',
                'required' => false,
                'description' => 'Current map zoom level (minimum 13 to show results)',
            ],
        ];
    }
}
