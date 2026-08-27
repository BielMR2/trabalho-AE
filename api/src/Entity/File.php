<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity]
#[ApiResource(
    types: ['https://schema.org/MediaObject'],
    operations: [
        new Get(),
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data']],
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary'
                                    ]
                                ]
                            ]
                        ]
                    ])
                )
            )
        )
    ],
    outputFormats: ['jsonld' => ['application/ld+json']],
    normalizationContext: ['groups' => ['file:read']]
)]
class File
{
    /**
     * @see https://schema.org/identifier
     */
    #[ApiProperty(identifier: true, types: ['https://schema.org/identifier'])]
    #[Groups(groups: ['Evaluation:read', 'Establishments:read', 'Establishments:read:admin'])]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    private Uuid $id;

    /**
     * @see https://schema.org/contentUrl
     */
    #[ApiProperty(writable: false, types: ['https://schema.org/contentUrl'])]
    #[Groups(['file:read'])]
    public ?string $contentUrl = null;

    /**
     * @see https://schema.org/contentUrl
     */
    #[ApiProperty(types: ['https://schema.org/contentUrl'])]
    #[Vich\UploadableField(mapping: 'file', fileNameProperty: 'filePath')]
    #[Assert\NotNull]
    public ?SymfonyFile $file = null;

    /**
     * @see https://schema.org/contentUrl
     */
    #[ApiProperty(writable: false, types: ['https://schema.org/contentUrl'])]
    #[ORM\Column(nullable: true)]
    public ?string $filePath = null;

    public function getId(): Uuid
    {
        return $this->id;
    }
}