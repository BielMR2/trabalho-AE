<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Enum\Criterion;
use App\Enum\CriterionEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
  public function __construct()
  {
    $this->votes = new ArrayCollection();
    $this->images = new ArrayCollection();
  }

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

  #[ApiProperty(example: '/criterion_enums/wheelchair_accessible', types: ['https://schema.org/Property'])]
  #[Assert\NotNull]
  #[Groups(['Evaluation:read', 'Evaluation:write'])]
  #[ORM\Column(type: Types::STRING, enumType: CriterionEnum::class)]
  public CriterionEnum $criterion;

  #[ApiProperty(example: 10)]
  #[Groups(['Evaluation:read', 'Evaluation:write'])]
  #[Assert\Range(min: 0, max: 10)]
  #[ORM\Column(type: Types::SMALLINT)]
  public int $rating;

  /**
   * @var Collection<int, EvaluationVote>
   */
  #[ORM\OneToMany(targetEntity: EvaluationVote::class, mappedBy: 'evaluationRating', cascade: ['persist'])]
  public Collection $votes;

  #[ApiProperty(description: 'Current authenticated user vote: 1 (upvote), -1 (downvote), or null (not voted)')]
  #[Groups(['Evaluation:read'])]
  public ?int $currentUserVote = null;

  /**
   * @var Collection<int, Image>
   */
  #[ApiProperty(types: ['https://schema.org/image'])]
  #[ORM\ManyToMany(targetEntity: Image::class)]
  #[ORM\JoinTable(name: 'evaluation_rating_image')]
  #[Groups(['Evaluation:read', 'Evaluation:write'])]
  public Collection $images;

  public function getId(): Uuid
  {
    return $this->id;
  }

  /**
   * Soma algébrica de todos os votos (+1/-1) neste rating.
   */
  #[Groups(['Evaluation:read', 'Establishments:read', 'Establishments:read:admin'])]
  public function getNetVotes(): int
  {
    $net = 0;
    foreach ($this->votes as $vote) {
      $net += $vote->value;
    }
    return $net;
  }

  public function addImage(Image $image): self
  {
    if (!$this->images->contains($image)) {
      $this->images->add($image);
    }

    return $this;
  }

  public function removeImage(Image $image): self
  {
    $this->images->removeElement($image);

    return $this;
  }
}
