<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** The visitor's language when we have it (lang/pt_BR.json), English otherwise. */
class PreferredLanguage
{
    public function handle(Request $request, Closure $next): Response
    {
        $preferred = $request->getPreferredLanguage(['en', 'pt_BR', 'pt']);
        app()->setLocale(str_starts_with((string) $preferred, 'pt') ? 'pt_BR' : 'en');

        return $next($request);
    }
}
