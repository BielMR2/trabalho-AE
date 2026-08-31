<?php

declare(strict_types=1);

namespace App\Enum;

use ApiPlatform\Metadata\Operation;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;

trait EnumApiResourceTrait
{
    public function getId(): string
    {
        return $this->value;
    }

    #[Groups('Enum:read')]
    public function getValue(): string
    {
        return $this->value;
    }

    #[Ignore]
    public static function getCases(): array
    {
        return self::cases();
    }

    public static function getCase(Operation $operation, array $uriVariables): ?static
    {
        $id = $uriVariables['id'] ?? null;

        return self::tryFrom($id);
    }
}
