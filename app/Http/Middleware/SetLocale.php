<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $locale = $user?->locale
            ?: $user?->parentProfile?->preferred_language
            ?: config('app.locale');

        if (in_array($locale, config('sats.locales'), true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
