<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UsePortalLocale
{
    /**
     * Render the portal in the language chosen for the signed-in client user.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($locale = $request->user()?->locale) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
