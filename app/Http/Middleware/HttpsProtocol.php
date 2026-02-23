<?php

namespace App\Http\Middleware;

use Closure;

class HttpsProtocol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $force = env('FORCE_HTTPS');
        $isSecure = $request->secure();
        $proto = $request->header('X-Forwarded-Proto');

        // Log for debugging (will appear in railway logs)
        error_log("HttpsProtocol DEBUG: URI=" . $request->getRequestUri() . ", isSecure=" . ($isSecure ? 'YES' : 'NO') . ", Proto=$proto");

        if ($force == "On" && !$isSecure) {
            error_log("HttpsProtocol: REDIRECTING TO HTTPS");
            return redirect()->secure($request->getRequestUri());
        }

        $response = $next($request);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            error_log("REDIRECT DETECTED to: " . $response->getTargetUrl());
        }
        return $response;
    }
}
