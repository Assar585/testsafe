<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Session;
use Config;
use Carbon\Carbon;
use App\Models\Language;
use App\Services\GeoLocationService;
use Illuminate\Support\Facades\URL;

class Localization
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
        // 1. Check URL for locale
        $locale = $request->segment(1);
        $languages = [];
        try {
            $languages = Language::where('is_active', 1)->pluck('code')->toArray();
        } catch (\Exception $e) {
            try {
                $languages = Language::pluck('code')->toArray();
            } catch (\Exception $e2) {
                $languages = ['en', 'ru']; // Emergency fallback
            }
        }

        // If the first segment is a valid lang code
        if (in_array($locale, $languages)) {
            App::setLocale($locale);
            Session::put('locale', $locale);

            // Set default for URL generator so we don't have to pass it everywhere
            URL::defaults(['locale' => $locale]);

            // Update langcode for Carbon and other needs
            $langObj = Language::where('code', $locale)->first();
            if ($langObj) {
                Session::put('langcode', $langObj->app_lang_code);
                Carbon::setLocale($langObj->app_lang_code);
            }

            return $next($request);
        }

        // 2. If no locale in URL, determine the best one
        $determinedLocale = $this->determineLocale($request);

        // 3. Redirect to the prefixed URL if it's a frontend request
        // (Avoid redirecting for admin, api, or ajax requests)
        if ($this->shouldRedirect($request)) {
            $segments = $request->segments();
            array_unshift($segments, $determinedLocale);
            return redirect()->to(implode('/', $segments) . ($request->getQueryString() ? '?' . $request->getQueryString() : ''));
        }

        // Fallback for cases where we don't redirect
        App::setLocale($determinedLocale);
        return $next($request);
    }

    /**
     * Determine locale based on priority: Session -> Browser -> IP -> Default
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
                if (Language::where('code', $browserLang)->exists()) {
                    return $browserLang;
                }
            }
        }

        // 3. IP-Geo
        $ip = $request->ip();
        $countryCode = $this->geoLocationService->getCountryCode($ip);
        if ($countryCode) {
            $geoLang = $this->geoLocationService->getLanguageByCountry($countryCode);
            if ($geoLang && Language::where('code', $geoLang)->where('is_active', 1)->exists()) {
                return $geoLang;
            }
        }

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

    /**
     * Check if the request should be redirected to a prefixed URL
     */
    protected function shouldRedirect($request)
    {
        // Don't redirect if it's an AJAX request, an API request, or an Admin request
        if ($request->ajax() || $request->is('api/*') || $request->is('admin/*') || $request->is('seller/*')) {
            return false;
        }

        // Add more exclusions as needed (e.g., webhooks, uploads)
        $exclusions = ['aiz-uploader*', 'social-login*', 'apple-callback*', 'sitemap.xml', 'force-cache-clear*', 'db-update*'];
        foreach ($exclusions as $exclusion) {
            if ($request->is($exclusion)) {
                return false;
            }
        }

        return true;
    }
}
