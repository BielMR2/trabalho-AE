<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\ImageFactory;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class ImageTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    #[Test]
    public function asAnonymousICanUploadAnImage(): void
    {
        $file = new UploadedFile(
            __DIR__ . '/fixtures/test.jpg',
            'test.jpg',
            'image/jpeg',
            null,
            true
        );

        $response = $this->client->request('POST', '/images', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => [
                    'file' => $file,
                ],
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/Image/item.json'));
    }

    #[Test]
    public function asAnonymousICanGetAnImage(): void
    {
        $imageEntity = ImageFactory::createOne();

        $response = $this->client->request('GET', '/images/' . $imageEntity->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/Image/item.json'));
    }
}
