<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Language;
use App\Services\GeoLocationService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class GeoLocaleMiddleware
{
    protected $geoLocationService;

    public function __construct(GeoLocationService $geoLocationService)
    {
        $this->geoLocationService = $geoLocationService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // If locale is already in session (manual choice or previous auto-detection), we trust it.
        if (Session::has('locale')) {
            return $next($request);
        }

        // Detect country from IP
        $ip = $request->ip();
        $countryCode = $this->geoLocationService->getCountryCode($ip);

        if ($countryCode) {
            // Get the preferred language code for this country
            $preferredLocale = $this->geoLocationService->getLanguageByCountry($countryCode);

            // Check if this language exists and is active on the site
            $language = Language::where('code', $preferredLocale)->where('status', 1)->first();

            if ($language) {
                App::setLocale($language->code);
                Session::put('locale', $language->code);
                Session::put('langcode', $language->app_lang_code);
            } else {
                // Fallback to default if preferred is not active
                $defaultLocale = env('DEFAULT_LANGUAGE', 'en');
                App::setLocale($defaultLocale);
                Session::put('locale', $defaultLocale);

                $defaultLanguage = Language::where('code', $defaultLocale)->first();
                if ($defaultLanguage) {
                    Session::put('langcode', $defaultLanguage->app_lang_code);
                }
            }
        }

        return $next($request);
    }
}
