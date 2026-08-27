<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\Factory\FileFactory;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class FileTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    #[Test]
    public function asAnonymousICanUploadAFile(): void
    {
        $file = new UploadedFile(
            __DIR__ . '/fixtures/test.txt',
            'test.txt',
            'text/plain',
            null,
            true
        );

        $response = $this->client->request('POST', '/files', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => [
                    'file' => $file,
                ],
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/File/item.json'));
    }

    #[Test]
    public function asAnonymousICanGetAFile(): void
    {
        $fileEntity = FileFactory::createOne();

        $response = $this->client->request('GET', '/files/' . $fileEntity->getId());

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertMatchesJsonSchema(file_get_contents(__DIR__ . '/schemas/File/item.json'));
    }
}
