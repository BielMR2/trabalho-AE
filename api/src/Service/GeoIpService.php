<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * GeoIP lookup service using a free public API (ip-api.com).
 * Caches lookups to avoid repeated external calls for the same IP.
 */
final class GeoIpService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const API_URL = 'http://ip-api.com/json/';

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
    ) {

    }

    /**
     * Returns the ISO-3166-1 alpha-2 country code for the given IP address,
     * or null if the lookup fails or the IP is private.
     */
    public function getCountryCode(string $ip): ?string
    {
        // Private or local IPs
        if (str_starts_with($ip, '127.') || str_starts_with($ip, '10.') || str_starts_with($ip, '192.168.') || str_starts_with($ip, '172.')) {
            return null;
        }

        $cacheKey = 'geoip_' . md5($ip);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($ip): ?string {
            $item->expiresAfter(self::CACHE_TTL);

            try {
                $response = $this->httpClient->request('GET', self::API_URL . $ip, ['timeout' => 2.0]);
                $data = $response->toArray();

                if (isset($data['status']) && $data['status'] === 'success' && isset($data['countryCode'])) {
                    return $data['countryCode'];
                }
            } catch (\Exception $e) {
                // Fail-open: return null if the external service is unavailable
            }

            return null;
        });
    }
}
