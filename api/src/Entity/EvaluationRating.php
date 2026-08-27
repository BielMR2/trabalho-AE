<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Enum\Criterion;
use App\Enum\CriterionEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

// We don't expose this directly via API since it's embedded in Evaluation POST
#[ApiResource(
  operations: [],
)]
#[ORM\Entity]
class EvaluationRating
{
  #[ApiProperty(identifier: true)]
  #[Groups(['Evaluation:read'])]
  #[ORM\Column(type: UuidType::NAME, unique: true)]
  #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\Id]
  private Uuid $id;

  #[ApiProperty(example: "/evaluations/{id}", types: ['https://schema.org/object'])]
  #[ORM\ManyToOne(targetEntity: Evaluation::class, inversedBy: 'ratings')]
  #[ORM\JoinColumn(nullable: false)]
  public Evaluation $evaluation;

  #[ApiProperty(example: CriterionEnum::WheelchairAccessible->value, types: ['https://schema.org/Property'])]
  #[Assert\NotNull]
  #[Groups(['Evaluation:read', 'Evaluation:write'])]
  #[ORM\Column(type: Types::STRING, enumType: CriterionEnum::class)]
  public CriterionEnum $criterion;

  #[ApiProperty(example: 10)]
  #[Groups(['Evaluation:read', 'Evaluation:write'])]
  #[Assert\Range(min: 0, max: 10)]
  #[ORM\Column(type: Types::SMALLINT)]
  public int $rating;

  public function getId(): Uuid
  {
    return $this->id;
  }
}
