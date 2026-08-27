<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\Evaluation;
use App\Repository\EvaluationRepository;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;
use function Zenstruck\Foundry\lazy;

/**
 * @method        Evaluation|Proxy                                         create(array|callable $attributes = [])
 * @method static Evaluation|Proxy                                         createOne(array $attributes = [])
 * @method static Evaluation|Proxy                                         find(object|array|mixed $criteria)
 * @method static Evaluation|Proxy                                         findOrCreate(array $attributes)
 * @method static Evaluation|Proxy                                         first(string $sortedField = 'id')
 * @method static Evaluation|Proxy                                         last(string $sortedField = 'id')
 * @method static Evaluation|Proxy                                         random(array $attributes = [])
 * @method static Evaluation|Proxy                                         randomOrCreate(array $attributes = [])
 * @method static Evaluation[]|Proxy[]                                     all()
 * @method static Evaluation[]|Proxy[]                                     createMany(int $number, array|callable $attributes = [])
 * @method static Evaluation[]|Proxy[]                                     createSequence(iterable|callable $sequence)
 * @method static Evaluation[]|Proxy[]                                     findBy(array $attributes)
 * @method static Evaluation[]|Proxy[]                                     randomRange(int $min, int $max, array $attributes = [])
 * @method static Evaluation[]|Proxy[]                                     randomSet(int $number, array $attributes = [])
 * @method        FactoryCollection<Evaluation|Proxy>                      many(int $min, int|null $max = null)
 * @method        FactoryCollection<Evaluation|Proxy>                      sequence(iterable|callable $sequence)
 * @method static ProxyRepositoryDecorator<Evaluation, EvaluationRepository> repository()
 *
 * @phpstan-method Evaluation&Proxy<Evaluation> create(array|callable $attributes = [])
 * @phpstan-method static Evaluation&Proxy<Evaluation> createOne(array $attributes = [])
 * @phpstan-method static Evaluation&Proxy<Evaluation> find(object|array|mixed $criteria)
 * @phpstan-method static Evaluation&Proxy<Evaluation> findOrCreate(array $attributes)
 * @phpstan-method static Evaluation&Proxy<Evaluation> first(string $sortedField = 'id')
 * @phpstan-method static Evaluation&Proxy<Evaluation> last(string $sortedField = 'id')
 * @phpstan-method static Evaluation&Proxy<Evaluation> random(array $attributes = [])
 * @phpstan-method static Evaluation&Proxy<Evaluation> randomOrCreate(array $attributes = [])
 * @phpstan-method static list<Evaluation&Proxy<Evaluation>> all()
 * @phpstan-method static list<Evaluation&Proxy<Evaluation>> createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static list<Evaluation&Proxy<Evaluation>> createSequence(iterable|callable $sequence)
 * @phpstan-method static list<Evaluation&Proxy<Evaluation>> findBy(array $attributes)
 * @phpstan-method static list<Evaluation&Proxy<Evaluation>> randomRange(int $min, int $max, array $attributes = [])
 * @phpstan-method static list<Evaluation&Proxy<Evaluation>> randomSet(int $number, array $attributes = [])
 * @phpstan-method FactoryCollection<Evaluation&Proxy<Evaluation>> many(int $min, int|null $max = null)
 * @phpstan-method FactoryCollection<Evaluation&Proxy<Evaluation>> sequence(iterable|callable $sequence)
 *
 * @extends PersistentObjectFactory<Evaluation>
 */
final class EvaluationFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array
    {
        return [
            'comment' => self::faker()->text(),
            'establishment' => lazy(static fn (): EstablishmentFactory => EstablishmentFactory::new()),
        ];
    }

    public static function class(): string
    {
        return Evaluation::class;
    }
}
