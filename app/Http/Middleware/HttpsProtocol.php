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
        error_log("HttpsProtocol DEBUG: FORCE_HTTPS=$force, isSecure=" . ($isSecure ? 'YES' : 'NO') . ", X-Forwarded-Proto=$proto");

        if ($force == "On" && !$isSecure) {
            return redirect()->secure($request->getRequestUri());
        }
        return $next($request);
    }
}
