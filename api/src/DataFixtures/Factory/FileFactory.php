<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\File;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;

/**
 * @method        File|Proxy                                         create(array|callable $attributes = [])
 * @method static File|Proxy                                         createOne(array $attributes = [])
 * @method static File|Proxy                                         find(object|array|mixed $criteria)
 * @method static File|Proxy                                         findOrCreate(array $attributes)
 * @method static File|Proxy                                         first(string $sortedField = 'id')
 * @method static File|Proxy                                         last(string $sortedField = 'id')
 * @method static File|Proxy                                         random(array $attributes = [])
 * @method static File|Proxy                                         randomOrCreate(array $attributes = [])
 * @method static File[]|Proxy[]                                     all()
 * @method static File[]|Proxy[]                                     createMany(int $number, array|callable $attributes = [])
 * @method static File[]|Proxy[]                                     createSequence(iterable|callable $sequence)
 * @method static File[]|Proxy[]                                     findBy(array $attributes)
 * @method static File[]|Proxy[]                                     randomRange(int $min, int $max, array $attributes = [])
 * @method static File[]|Proxy[]                                     randomSet(int $number, array $attributes = [])
 * @method        FactoryCollection<File|Proxy>                      many(int $min, int|null $max = null)
 * @method        FactoryCollection<File|Proxy>                      sequence(iterable|callable $sequence)
 *
 * @phpstan-method File&Proxy<File> create(array|callable $attributes = [])
 * @phpstan-method static File&Proxy<File> createOne(array $attributes = [])
 * @phpstan-method static File&Proxy<File> find(object|array|mixed $criteria)
 * @phpstan-method static File&Proxy<File> findOrCreate(array $attributes)
 * @phpstan-method static File&Proxy<File> first(string $sortedField = 'id')
 * @phpstan-method static File&Proxy<File> last(string $sortedField = 'id')
 * @phpstan-method static File&Proxy<File> random(array $attributes = [])
 * @phpstan-method static File&Proxy<File> randomOrCreate(array $attributes = [])
 * @phpstan-method static list<File&Proxy<File>> all()
 * @phpstan-method static list<File&Proxy<File>> createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static list<File&Proxy<File>> createSequence(iterable|callable $sequence)
 * @phpstan-method static list<File&Proxy<File>> findBy(array $attributes)
 * @phpstan-method static list<File&Proxy<File>> randomRange(int $min, int $max, array $attributes = [])
 * @phpstan-method static list<File&Proxy<File>> randomSet(int $number, array $attributes = [])
 * @phpstan-method FactoryCollection<File&Proxy<File>> many(int $min, int|null $max = null)
 * @phpstan-method FactoryCollection<File&Proxy<File>> sequence(iterable|callable $sequence)
 *
 * @extends PersistentObjectFactory<File>
 */
final class FileFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array
    {
        return [
            'filePath' => self::faker()->word() . '.txt',
        ];
    }

    public static function class(): string
    {
        return File::class;
    }
}
