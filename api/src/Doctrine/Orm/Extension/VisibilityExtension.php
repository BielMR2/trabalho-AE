<?php

namespace App\Doctrine\Orm\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Traits\RegisterActiveTrait;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class VisibilityExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
  public function __construct(
    private readonly Security $security,
  ) {
  }

  public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
  {
    $this->addWhere($queryBuilder, $resourceClass);
  }

  public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
  {
    $this->addWhere($queryBuilder, $resourceClass);
  }

  private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
  {
    if (
      $this->security->isGranted('ROLE_SUPER_ADMIN') || $this->security->isGranted('ROLE_ADMIN') || !$this->usesActiveTrait($resourceClass)
    ) {
      return;
    }

    $rootAlias = $queryBuilder->getRootAliases()[0];
    $queryBuilder->andWhere(sprintf('%s.active = true', $rootAlias));
  }

  private function usesActiveTrait(string $class): bool
  {
    return in_array(RegisterActiveTrait::class, class_uses($class) ?: [], true);
  }
}