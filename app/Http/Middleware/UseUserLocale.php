<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseUserLocale
{
    /**
     * Render pages and Livewire updates in the signed-in user's language.
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
