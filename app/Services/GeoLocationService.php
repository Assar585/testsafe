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
                // Reduced timeout to 2 seconds to avoid 502/504 on slow external APIs
                $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}");
                if ($response->successful()) {
                    $details = $response->json();
                    if ($details && isset($details['status']) && $details['status'] === 'success') {
                        return strtoupper($details['countryCode']);
                    }
                }
            } catch (\Exception $e) {
                // Return null but log the error so we know if the service is down
                Log::warning("GeoLocationService failed for IP {$ip}: " . $e->getMessage());
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

        return $mapping[$countryCode] ?? null;
    }

    /**
     * Get preferred language from browser headers
     */
    public function getBrowserLanguage($request)
    {
        $browserLang = $request->getPreferredLanguage(); // Laravel helper for Accept-Language
        if ($browserLang) {
            $code = substr($browserLang, 0, 2);
            return $code;
        }
        return null;
    }
}
