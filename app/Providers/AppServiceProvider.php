<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    Schema::defaultStringLength(191);
    Paginator::useBootstrap();

    // Add a local listener to print query execution times and counts to laravel.log
    // so we can see what's blocking TTFB.
    DB::listen(function ($query) {
      \Log::info($query->sql, ['time' => $query->time]);
    });

    // Self-healing nav link fix:
    // If the stored nav links still point to the old production domain,
    // replace them with the current APP_URL. Runs on first request only
    // (becomes a no-op once DB is updated). Bypasses route/cache issues.
    $this->fixNavLinksIfNeeded();
  }

  /**
   * Checks if header_menu_links in DB still contains old domain and fixes it.
   */
  protected function fixNavLinksIfNeeded(): void
  {
    try {
      $currentOrigin = rtrim(config('app.url'), '/');
      $currentHost = parse_url($currentOrigin, PHP_URL_HOST);

      if (!$currentHost)
        return;

      $setting = \App\Models\Setting::where('type', 'header_menu_links')->first();

      if (!$setting || !$setting->value)
        return;

      // Only do work if there's a domain that's NOT our current host
      if (!preg_match('#https?://(?!' . preg_quote($currentHost, '#') . ')[^/"\']+#', $setting->value)) {
        return; // Already correct — no-op
      }

      // Replace any http/https origin that isn't our current host
      $fixed = preg_replace_callback(
        '#https?://([^/"\'\\s]+)#',
        function ($match) use ($currentHost, $currentOrigin) {
          $urlHost = parse_url($match[0], PHP_URL_HOST);
          if ($urlHost && $urlHost !== $currentHost) {
            return preg_replace('#https?://' . preg_quote($urlHost, '#') . '#', $currentOrigin, $match[0]);
          }
          return $match[0];
        },
        $setting->value
      );

      if ($fixed !== $setting->value) {
        $setting->value = $fixed;
        $setting->save();
        // Flush cached setting so the new value is served immediately
        \Illuminate\Support\Facades\Cache::forget('setting.header_menu_links');
      }
    } catch (\Throwable $e) {
      // Silently fail if DB is unavailable during boot
    }
  }

  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    //
  }
}

