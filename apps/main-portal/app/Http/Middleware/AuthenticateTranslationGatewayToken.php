<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateTranslationGatewayToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('services.translation_gateway.token');

        abort_unless(filled($expectedToken), 503, 'Translation gateway token is not configured.');
        abort_unless(hash_equals((string) $expectedToken, (string) $request->bearerToken()), 401);

        return $next($request);
    }
}
