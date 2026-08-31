<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Repository\EvaluationRepository;
use App\State\Processor\EvaluationPersistProcessor;
use App\Traits\RegisterActiveTrait;
use App\Traits\RegisterDateTimeTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as AppAssert;

/**
 * @see https://schema.org/Review
 */
#[ApiResource(
  uriTemplate: '/admin/evaluations{._format}',
  shortName: 'AdminEvaluations',
  types: ['https://schema.org/Review'],
  operations: [
    new Delete(
      uriTemplate: '/admin/evaluations/{id}{._format}',
    ),
    new Patch(
      uriTemplate: '/admin/evaluations/{id}{._format}',
    )
  ],
  normalizationContext: [
    AbstractNormalizer::GROUPS => ['Evaluation:read:admin', 'DateTime:read', 'Active:read'],
    AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
  ],
  security: 'is_granted("OIDC_ADMIN")',
  mercure: [
    'topics' => [
      '@=iri(object, ' . UrlGeneratorInterface::ABS_URL . ', get_operation(object, "/admin/evaluations/{id}{._format}"))',
    ],
  ]
)]
#[ApiResource(
  types: ['https://schema.org/Review'],
  operations: [
    new GetCollection(
      uriTemplate: '/evaluations{._format}',
    ),
    new Post(
      uriTemplate: '/evaluations{._format}',
      processor: EvaluationPersistProcessor::class,
      security: 'is_granted("OIDC_USER")'
    ),
    new Get(
      uriTemplate: '/evaluations/{id}{._format}'
    )
  ],
  normalizationContext: [
    AbstractNormalizer::GROUPS => ['Evaluation:read'],
    AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
  ],
  denormalizationContext: [
    AbstractNormalizer::GROUPS => ['Evaluation:write'],
  ],
  mercure: [
    'topics' => [
      '@=iri(object, ' . UrlGeneratorInterface::ABS_URL . ', get_operation(object, "/evaluations/{id}{._format}"))',
    ],
  ]
)]
#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
class Evaluation
{
  use RegisterDateTimeTrait;
  use RegisterActiveTrait;

  public function __construct()
  {
    $this->ratings = new ArrayCollection();
    $this->votes = new ArrayCollection();
  }

  #[ApiProperty(identifier: true, types: ['https://schema.org/identifier'])]
  #[Groups(['Evaluation:read', 'Establishments:read', 'Establishments:read:admin'])]
  #[ORM\Column(type: UuidType::NAME, unique: true)]
  #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\Id]
  private Uuid $id;

  #[ApiProperty(example: 'Ótima experiência!', types: ['https://schema.org/text'])]
  #[Groups(groups: ['Evaluation:read', 'Evaluation:write', 'Establishments:read', 'Establishments:read:admin'])]
  #[AppAssert\OpenAiModeration]
  #[ORM\Column(type: Types::TEXT, nullable: true)]
  public ?string $comment = null;

  #[Groups(groups: ['Evaluation:read', 'Evaluation:write', 'Establishments:read', 'Establishments:read:admin'])]
  #[ApiProperty(example: '/evaluation_criteria/{id}')]
  #[ORM\OneToMany(targetEntity: EvaluationRating::class, mappedBy: 'evaluation', cascade: ['persist', 'remove'])]
  public Collection $ratings;

  #[Groups(groups: ['Evaluation:read'])]
  #[ORM\OneToMany(targetEntity: EvaluationVote::class, mappedBy: 'evaluation', cascade: ['persist', 'remove'])]
  public Collection $votes;

  #[ApiProperty(example: '/establishments/{id}', types: ['https://schema.org/object'])]
  #[Groups(groups: ['Evaluation:read'])]
  #[ORM\ManyToOne(targetEntity: Establishment::class, inversedBy: 'evaluations')]
  #[ORM\JoinColumn(nullable: false)]
  public Establishment $establishment;

  #[ApiProperty(writable: true)]
  #[Groups(['Evaluation:write'])]
  public ?string $establishmentGooglePlaceId = null;

  public function getId(): Uuid
  {
    return $this->id;
  }

  #[Groups(['Evaluation:read', 'Establishments:read', 'Establishments:read:admin'])]
  public function getNetVotes(): int
  {
      $net = 0;
      foreach ($this->votes as $vote) {
          $net += $vote->value;
      }
      return $net;
  }

  public function addRating(EvaluationRating $rating): self
  {
      if (!$this->ratings->contains($rating)) {
          $this->ratings->add($rating);
          $rating->evaluation = $this;
      }

      return $this;
  }

  public function removeRating(EvaluationRating $rating): self
  {
      $this->ratings->removeElement($rating);

      return $this;
  }
}
