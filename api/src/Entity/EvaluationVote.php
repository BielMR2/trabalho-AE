<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\State\Processor\EvaluationVotePersistProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Voto em um rating individual de uma avaliação.
 * Cada usuário pode ter no máximo 1 voto por EvaluationRating (upsert).
 *
 * @see https://schema.org/Rating
 */
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/evaluation_votes{._format}',
            processor: EvaluationVotePersistProcessor::class,
            security: 'is_granted("OIDC_USER")'
        ),
        new GetCollection(security: 'is_granted("OIDC_USER")')
    ],
    normalizationContext: ['groups' => ['EvaluationVote:read']],
    denormalizationContext: ['groups' => ['EvaluationVote:write']]
)]
#[ORM\Entity]
#[UniqueEntity(fields: ['evaluationRating', 'user'], message: 'You have already voted for this rating.')]
class EvaluationVote
{
    #[ApiProperty(identifier: true)]
    #[Groups(['EvaluationVote:read', 'Evaluation:read'])]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    private Uuid $id;

    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[ApiProperty(types: ['https://schema.org/object'])]
    #[Groups(['EvaluationVote:write', 'EvaluationVote:read'])]
    #[ORM\ManyToOne(targetEntity: EvaluationRating::class, inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    public EvaluationRating $evaluationRating;

    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[ApiProperty(types: ['https://schema.org/author'])]
    #[Groups(['EvaluationVote:read'])]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    public User $user;

    #[ApiProperty(description: '1 for upvote, -1 for downvote')]
    #[Groups(['EvaluationVote:write', 'EvaluationVote:read', 'Evaluation:read'])]
    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Choice(choices: [1, -1], message: 'Vote value must be either 1 (upvote) or -1 (downvote).')]
    public int $value;

    public function getId(): Uuid
    {
        return $this->id;
    }
}
