<?php

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) ($request->route('locale') ?: Locales::default());

        abort_unless(Locales::isSupported($locale), 404);

        App::setLocale($locale);
        $request->route()?->forgetParameter('locale');

        return $next($request);
    }
}
