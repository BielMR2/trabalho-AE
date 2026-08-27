<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\Image;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;

/**
 * @method        Image|Proxy                                         create(array|callable $attributes = [])
 * @method static Image|Proxy                                         createOne(array $attributes = [])
 * @method static Image|Proxy                                         find(object|array|mixed $criteria)
 * @method static Image|Proxy                                         findOrCreate(array $attributes)
 * @method static Image|Proxy                                         first(string $sortedField = 'id')
 * @method static Image|Proxy                                         last(string $sortedField = 'id')
 * @method static Image|Proxy                                         random(array $attributes = [])
 * @method static Image|Proxy                                         randomOrCreate(array $attributes = [])
 * @method static Image[]|Proxy[]                                     all()
 * @method static Image[]|Proxy[]                                     createMany(int $number, array|callable $attributes = [])
 * @method static Image[]|Proxy[]                                     createSequence(iterable|callable $sequence)
 * @method static Image[]|Proxy[]                                     findBy(array $attributes)
 * @method static Image[]|Proxy[]                                     randomRange(int $min, int $max, array $attributes = [])
 * @method static Image[]|Proxy[]                                     randomSet(int $number, array $attributes = [])
 * @method        FactoryCollection<Image|Proxy>                      many(int $min, int|null $max = null)
 * @method        FactoryCollection<Image|Proxy>                      sequence(iterable|callable $sequence)
 *
 * @phpstan-method Image&Proxy<Image> create(array|callable $attributes = [])
 * @phpstan-method static Image&Proxy<Image> createOne(array $attributes = [])
 * @phpstan-method static Image&Proxy<Image> find(object|array|mixed $criteria)
 * @phpstan-method static Image&Proxy<Image> findOrCreate(array $attributes)
 * @phpstan-method static Image&Proxy<Image> first(string $sortedField = 'id')
 * @phpstan-method static Image&Proxy<Image> last(string $sortedField = 'id')
 * @phpstan-method static Image&Proxy<Image> random(array $attributes = [])
 * @phpstan-method static Image&Proxy<Image> randomOrCreate(array $attributes = [])
 * @phpstan-method static list<Image&Proxy<Image>> all()
 * @phpstan-method static list<Image&Proxy<Image>> createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static list<Image&Proxy<Image>> createSequence(iterable|callable $sequence)
 * @phpstan-method static list<Image&Proxy<Image>> findBy(array $attributes)
 * @phpstan-method static list<Image&Proxy<Image>> randomRange(int $min, int $max, array $attributes = [])
 * @phpstan-method static list<Image&Proxy<Image>> randomSet(int $number, array $attributes = [])
 * @phpstan-method FactoryCollection<Image&Proxy<Image>> many(int $min, int|null $max = null)
 * @phpstan-method FactoryCollection<Image&Proxy<Image>> sequence(iterable|callable $sequence)
 *
 * @extends PersistentObjectFactory<Image>
 */
final class ImageFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array
    {
        return [
            'filePath' => self::faker()->word() . '.jpg',
        ];
    }

    public static function class(): string
    {
        return Image::class;
    }
}
