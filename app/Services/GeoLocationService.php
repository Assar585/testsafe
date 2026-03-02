<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Cache;

class GeoLocationService
{
    /**
     * Get country code from IP address.
     *
     * @param string $ip
     * @return string|null
     */
    public function getCountryCode($ip)
    {
        // Cache the IP lookup for 24 hours to reduce API calls
        return Cache::remember('geoip_country_' . $ip, 86400, function () use ($ip) {
            try {
                $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}");
                if ($response->successful()) {
                    $details = $response->json();
                    if ($details && isset($details['status']) && $details['status'] === 'success') {
                        return strtoupper($details['countryCode']);
                    }
                }
            } catch (\Exception $e) {
                Log::error("GeoLocationService error: " . $e->getMessage());
            }
            return null;
        });
    }

    /**
     * Map country code to language code based on common regions.
     *
     * @param string $countryCode
     * @return string
     */
    public function getLanguageByCountry($countryCode)
    {
        $mapping = [
            'RU' => 'ru',
            'KZ' => 'ru',
            'BY' => 'ru',
            'UA' => 'ru',
            'AM' => 'ru',
            'AZ' => 'ru',
            'KG' => 'ru',
            'UZ' => 'ru',
            'TJ' => 'ru',
            'TM' => 'ru',
            'SA' => 'sa',
            'AE' => 'sa',
            'EG' => 'sa',
            'KW' => 'sa',
            'QA' => 'sa',
            'OM' => 'sa',
            'BH' => 'sa',
            'JO' => 'sa',
        ];

        return $mapping[$countryCode] ?? env('DEFAULT_LANGUAGE', 'en');
    }
}
