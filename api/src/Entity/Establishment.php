<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use App\Filter\CriterionAverageFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Repository\EstablishmentRepository;
use App\State\Processor\EstablishmentRemoveProcessor;
use App\Traits\RegisterActiveTrait;
use App\Traits\RegisterDateTimeTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Uid\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @see https://schema.org/LocalBusiness
 */
#[ApiResource(
    uriTemplate: '/admin/establishments{._format}',
    shortName: 'AdminEstablishments',
    types: ['https://schema.org/LocalBusiness'],
    operations: [
        new Delete(
            uriTemplate: '/admin/establishments/{id}{._format}',
            processor: EstablishmentRemoveProcessor::class
        ),
        new Patch(
            uriTemplate: '/admin/establishments/{id}{._format}',
        )
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Establishments:read:admin', 'Evaluation:read', 'DateTime:read', 'Active:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    security: 'is_granted("OIDC_ADMIN")',
    mercure: [
        'topics' => [
            '@=iri(object, ' . UrlGeneratorInterface::ABS_URL . ', get_operation(object, "/admin/establishments/{id}{._format}"))',
        ],
    ],
)]
#[ApiResource(
    types: ['https://schema.org/LocalBusiness'],
    operations: [
        new GetCollection(
            uriTemplate: '/establishments{._format}',
            filters: ['establishment.evaluations.ratings.criterion']
        ),
        new Get(
            uriTemplate: '/establishments/{id}{._format}',
            normalizationContext: [
                AbstractNormalizer::GROUPS => ['Establishment:read', 'Evaluation:read'],
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            ]
        )
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Establishment:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['Establishment:write'],
    ],
    mercure: [
        'topics' => [
            '@=iri(object, ' . UrlGeneratorInterface::ABS_URL . ', get_operation(object, "/admin/establishments/{id}{._format}"))',
            '@=iri(object, ' . UrlGeneratorInterface::ABS_URL . ', get_operation(object, "/establishments/{id}{._format}"))',
        ],
    ],
)]
#[ApiFilter(CriterionAverageFilter::class)]
#[ORM\Entity(repositoryClass: EstablishmentRepository::class)]
#[UniqueEntity(fields: ['googlePlaceId'])]
class Establishment
{
    use RegisterDateTimeTrait;
    use RegisterActiveTrait;

    #[ApiProperty(identifier: true, types: ['https://schema.org/identifier'])]
    #[Groups(groups: ['Establishment:read:admin'])]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    private Uuid $id;

    #[ApiProperty(example: 'ChIJ0Qh_Ld89Xo4RJj6lF8sS9aA', types: ['https://schema.org/identifier'])]
    #[Groups(groups: ['Establishment:read', 'Establishment:read:admin', 'Establishment:write', 'Evaluation:read'])]
    #[ORM\Column(type: Types::TEXT, unique: true, nullable: true)]
    public ?string $googlePlaceId;

    #[ApiFilter(OrderFilter::class)]
    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    #[ApiProperty(example: 'Farol Shopping', types: ['https://schema.org/name'])]
    #[Groups(groups: ['Establishment:read', 'Establishment:read:admin', 'Establishment:write', 'Evaluation:read'])]
    #[ORM\Column(type: Types::TEXT)]
    public string $name;

    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    #[ApiProperty(types: ['https://schema.org/address'])]
    #[Groups(groups: ['Establishment:read', 'Establishment:read:admin', 'Establishment:write', 'Evaluation:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $address = null;

    #[ApiProperty(types: ['https://schema.org/telephone'])]
    #[Groups(groups: ['Establishment:read', 'Establishment:read:admin', 'Establishment:write', 'Evaluation:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $phoneNumber = null;

    #[ApiProperty(types: ['https://schema.org/url'])]
    #[Groups(groups: ['Establishment:read', 'Establishment:read:admin', 'Establishment:write', 'Evaluation:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $website = null;

    #[ApiProperty(example: 'POINT(-47.3999724 -20.518464)', types: ['https://schema.org/geo'])]
    #[Groups(groups: ['Establishment:read', 'Establishment:read:admin', 'Establishment:write', 'Evaluation:read'])]
    #[ORM\Column(type: 'geometry')]
    public mixed $location;

    /**
     * @var Collection<int, Evaluation>
     */
    #[Groups(groups: ['Establishment:read'])]
    #[ORM\OneToMany(targetEntity: Evaluation::class, mappedBy: 'establishment', cascade: ['remove'])]
    public Collection $evaluations;

    public function __construct()
    {
        $this->evaluations = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    #[Groups(groups: ['Establishment:read', 'Establishment:read:admin'])]
    public function getEvaluationsSummary(): array
    {
        $summary = [];
        $counts = [];

        foreach ($this->evaluations as $evaluation) {
            foreach ($evaluation->ratings as $rating) {
                // Excluir ratings individuais com reputação negativa
                if ($rating->getNetVotes() <= -3) {
                    continue;
                }

                $criterion = $rating->criterion->value;
                
                if (!isset($summary[$criterion])) {
                    $summary[$criterion] = 0;
                    $counts[$criterion] = 0;
                }
                
                $summary[$criterion] += $rating->rating;
                $counts[$criterion]++;
            }
        }

        $result = [];
        foreach ($summary as $criterion => $totalScore) {
            $result[$criterion] = [
                'average' => round($totalScore / $counts[$criterion], 2),
                'count' => $counts[$criterion]
            ];
        }

        return $result;
    }
}
