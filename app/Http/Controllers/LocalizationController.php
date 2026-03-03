<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Language;
use App\Services\GeoLocationService;
use Session;

class LocalizationController extends Controller
{
    protected $geoLocationService;

    public function __construct(GeoLocationService $geoLocationService)
    {
        $this->geoLocationService = $geoLocationService;
    }

    /**
     * Redirect root (/) to detected language prefix (/en, /ru, etc.)
     */
    public function redirect(Request $request)
    {
        $locale = $this->determineLocale($request);
        return redirect()->to('/' . $locale . ($request->getQueryString() ? '?' . $request->getQueryString() : ''));
    }

    /**
     * Re-use logic from middleware (though middleware handles prefix check)
     */
    protected function determineLocale($request)
    {
        // 1. Manual/Session
        if (Session::has('locale')) {
            return Session::get('locale');
        }

        // 2. Browser Accept-Language
        $browserLang = $this->geoLocationService->getBrowserLanguage($request);
        if ($browserLang) {
            try {
                if (Language::where('code', $browserLang)->where('is_active', 1)->exists()) {
                    return $browserLang;
                }
            } catch (\Exception $e) {
                try {
                    if (Language::where('code', $browserLang)->where('status', 1)->exists()) {
                        return $browserLang;
                    }
                } catch (\Exception $e2) {
                }
            }
        }

        // 3. IP-Geo (Commented out temporarily for performance/timeout investigation)
        /*
        $ip = $request->ip();
        $countryCode = $this->geoLocationService->getCountryCode($ip);
        if ($countryCode) {
            $geoLang = $this->geoLocationService->getLanguageByCountry($countryCode);
            if ($geoLang) {
                try {
                    if (Language::where('code', $geoLang)->where('is_active', 1)->exists()) {
                        return $geoLang;
                    }
                } catch (\Exception $e) {
                    try {
                        if (Language::where('code', $geoLang)->where('status', 1)->exists()) {
                            return $geoLang;
                        }
                    } catch (\Exception $e2) {}
                }
            }
        }
        */

        // 4. Default from DB
        try {
            $defaultLang = Language::where('is_default', 1)->where('is_active', 1)->first();
            if (!$defaultLang) {
                $defaultLang = Language::where('is_default', 1)->first();
            }
        } catch (\Exception $e) {
            $defaultLang = Language::first();
        }
        return $defaultLang ? $defaultLang->code : env('DEFAULT_LANGUAGE', 'en');
    }
}
