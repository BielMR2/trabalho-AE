<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GooglePlacesClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $googleApiKey
    ) {
    }

    /**
     * Fetches details for a Google Place ID using the Places API (New).
     *
     * @param string $placeId
     * @return array
     * @throws BadRequestHttpException
     */
    public function getPlaceDetails(string $placeId): array
    {
        $response = $this->httpClient->request('GET', 'https://places.googleapis.com/v1/places/' . $placeId, [
            'headers' => [
                'X-Goog-Api-Key' => $this->googleApiKey,
                'X-Goog-FieldMask' => 'id,displayName,location,formattedAddress,nationalPhoneNumber,websiteUri',
                'Accept-Language' => 'pt-BR'
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            $error = $response->toArray(false);
            throw new BadRequestHttpException('Invalid Google Place ID or unable to fetch details: ' . ($error['error']['message'] ?? 'Unknown error'));
        }

        return $response->toArray();
    }
}
