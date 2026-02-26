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
        // HTTPS redirect disabled - Railway handles HTTPS at the proxy level
        // Previously this created a redirect loop because Railway proxies HTTPS
        // but internal Nginx connection is HTTP, causing isSecure() = false even
        // after TrustProxies = '*' was set.
        return $next($request);
    }
}
