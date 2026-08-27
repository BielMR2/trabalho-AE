<?php

namespace App\Serializer;

use App\Entity\Image;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Storage\StorageInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ImageNormalizer implements NormalizerInterface
{

    private const string ALREADY_CALLED = 'MEDIA_OBJECT_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        #[Autowire(service: 'api_platform.jsonld.normalizer.item')]
        private readonly NormalizerInterface $normalizer,
        private readonly StorageInterface $storage,
        private readonly CacheManager $cacheManager,
    ) {
    }

    public function normalize($data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $context[self::ALREADY_CALLED] = true;

        $data->contentUrl = $this->storage->resolveUri($data, 'file');
        $data->contentUrlXs = $this->cacheManager->generateUrl($data->contentUrl, 'xs');
        $data->contentUrlSm = $this->cacheManager->generateUrl($data->contentUrl, 'sm');
        $data->contentUrlMd = $this->cacheManager->generateUrl($data->contentUrl, 'md');
        $data->contentUrlLg = $this->cacheManager->generateUrl($data->contentUrl, 'lg');

        return $this->normalizer->normalize($data, $format, $context);
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {

        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Image;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Image::class => true,
        ];
    }
}
