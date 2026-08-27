<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\Establishment;
use App\Repository\EstablishmentRepository;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @method        Establishment|Proxy                                         create(array|callable $attributes = [])
 * @method static Establishment|Proxy                                         createOne(array $attributes = [])
 * @method static Establishment|Proxy                                         find(object|array|mixed $criteria)
 * @method static Establishment|Proxy                                         findOrCreate(array $attributes)
 * @method static Establishment|Proxy                                         first(string $sortedField = 'id')
 * @method static Establishment|Proxy                                         last(string $sortedField = 'id')
 * @method static Establishment|Proxy                                         random(array $attributes = [])
 * @method static Establishment|Proxy                                         randomOrCreate(array $attributes = [])
 * @method static Establishment[]|Proxy[]                                     all()
 * @method static Establishment[]|Proxy[]                                     createMany(int $number, array|callable $attributes = [])
 * @method static Establishment[]|Proxy[]                                     createSequence(iterable|callable $sequence)
 * @method static Establishment[]|Proxy[]                                     findBy(array $attributes)
 * @method static Establishment[]|Proxy[]                                     randomRange(int $min, int $max, array $attributes = [])
 * @method static Establishment[]|Proxy[]                                     randomSet(int $number, array $attributes = [])
 * @method        FactoryCollection<Establishment|Proxy>                      many(int $min, int|null $max = null)
 * @method        FactoryCollection<Establishment|Proxy>                      sequence(iterable|callable $sequence)
 * @method static ProxyRepositoryDecorator<Establishment, EstablishmentRepository> repository()
 *
 * @phpstan-method Establishment&Proxy<Establishment> create(array|callable $attributes = [])
 * @phpstan-method static Establishment&Proxy<Establishment> createOne(array $attributes = [])
 * @phpstan-method static Establishment&Proxy<Establishment> find(object|array|mixed $criteria)
 * @phpstan-method static Establishment&Proxy<Establishment> findOrCreate(array $attributes)
 * @phpstan-method static Establishment&Proxy<Establishment> first(string $sortedField = 'id')
 * @phpstan-method static Establishment&Proxy<Establishment> last(string $sortedField = 'id')
 * @phpstan-method static Establishment&Proxy<Establishment> random(array $attributes = [])
 * @phpstan-method static Establishment&Proxy<Establishment> randomOrCreate(array $attributes = [])
 * @phpstan-method static list<Establishment&Proxy<Establishment>> all()
 * @phpstan-method static list<Establishment&Proxy<Establishment>> createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static list<Establishment&Proxy<Establishment>> createSequence(iterable|callable $sequence)
 * @phpstan-method static list<Establishment&Proxy<Establishment>> findBy(array $attributes)
 * @phpstan-method static list<Establishment&Proxy<Establishment>> randomRange(int $min, int $max, array $attributes = [])
 * @phpstan-method static list<Establishment&Proxy<Establishment>> randomSet(int $number, array $attributes = [])
 * @phpstan-method FactoryCollection<Establishment&Proxy<Establishment>> many(int $min, int|null $max = null)
 * @phpstan-method FactoryCollection<Establishment&Proxy<Establishment>> sequence(iterable|callable $sequence)
 *
 * @extends PersistentObjectFactory<Establishment>
 */
final class EstablishmentFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array
    {
        return [
            'name' => self::faker()->company(),
            'googlePlaceId' => self::faker()->unique()->md5(),
            'location' => sprintf('POINT(%f %f)', self::faker()->longitude(), self::faker()->latitude()),
        ];
    }

    public static function class(): string
    {
        return Establishment::class;
    }
}
