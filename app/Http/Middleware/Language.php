<?php

namespace App\Http\Middleware;

use App;
use Config;
use Closure;
use Session;
use Carbon\Carbon;
use App\Services\GeoLocationService;
use App\Models\Language as LanguageModel;

class Language
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
        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } else {
            $ip = $request->ip();
            $countryCode = $this->geoLocationService->getCountryCode($ip);
            $locale = null;

            if ($countryCode) {
                $preferredLocale = $this->geoLocationService->getLanguageByCountry($countryCode);

                // Check if this language is enabled in the admin panel
                $language = LanguageModel::where('code', $preferredLocale)->where('status', 1)->first();
                if ($language) {
                    $locale = $language->code;
                    $request->session()->put('langcode', $language->app_lang_code);
                }
            }

            if (!$locale) {
                $locale = env('DEFAULT_LANGUAGE', 'en');
                $defaultLanguage = LanguageModel::where('code', $locale)->first();
                if ($defaultLanguage) {
                    $request->session()->put('langcode', $defaultLanguage->app_lang_code);
                }
            }
        }

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        $langcode = Session::has('langcode') ? Session::get('langcode') : 'en';
        Carbon::setLocale($langcode);

        return $next($request);
    }
}
