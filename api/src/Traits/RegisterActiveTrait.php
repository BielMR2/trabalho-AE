<?php

namespace App\Traits;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait RegisterActiveTrait
{
  #[ApiProperty(types: ['https://schema.org/active'])]
  #[Groups(['Active:read'])]
  #[ORM\Column(type: Types::BOOLEAN)]
  protected ?bool $active = true;

  public function getActive(): ?bool
  {
    return $this->active;
  }

  public function toggleActive(): static
  {
    $this->active = !$this->active;

    return $this;
  }
}
