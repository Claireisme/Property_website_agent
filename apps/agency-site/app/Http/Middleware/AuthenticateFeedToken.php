<?php

namespace App\Http\Middleware;

use App\Models\FeedToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateFeedToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainTextToken = $request->bearerToken() ?: $request->query('token');

        if (blank($plainTextToken)) {
            return response()->json([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'Missing feed token',
                ],
            ], 401);
        }

        $feedToken = FeedToken::query()
            ->where('token_hash', FeedToken::hashToken($plainTextToken))
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $feedToken) {
            return response()->json([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'Invalid feed token',
                ],
            ], 401);
        }

        $feedToken->forceFill([
            'last_used_at' => now(),
        ])->saveQuietly();

        $request->attributes->set('feed_token', $feedToken);

        return $next($request);
    }
}
