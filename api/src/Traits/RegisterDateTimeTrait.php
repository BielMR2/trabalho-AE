<?php

namespace App\Traits;

use ApiPlatform\Metadata\ApiProperty;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait RegisterDateTimeTrait
{
    /**
     * @see https://schema.org/dateCreated
     */
    #[ApiProperty(types: ['https://schema.org/dateCreated'])]
    #[Groups(['DateTime:read'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected ?DateTime $createdAt = null;

    /**
     * @see https://schema.org/dateModified
     */
    #[ApiProperty(types: ['https://schema.org/dateModified'])]
    #[Groups(['DateTime:read'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected ?DateTime $updatedAt = null;

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

}
