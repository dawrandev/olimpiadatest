<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $defaultLocale = config('app.locale');
        $locale = session('locale', $defaultLocale);

        if (array_key_exists($locale, config('app.locales'))) {
            App::setLocale($locale);
        } else {
            App::setLocale($defaultLocale);
        }

        return $next($request);
    }
}
