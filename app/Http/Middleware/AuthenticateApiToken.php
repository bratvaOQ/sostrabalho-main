<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();

        if ($plain) {
            $token = ApiToken::where('token', hash('sha256', $plain))->first();

            if ($token && (!$token->expires_at || $token->expires_at->isFuture()) && $token->user) {
                // Só grava "último uso" no máximo 1x por minuto (evita escrita a cada request)
                if (!$token->last_used_at || $token->last_used_at->lt(now()->subMinute())) {
                    $token->forceFill(['last_used_at' => now()])->save();
                }

                Auth::setUser($token->user);
                $request->attributes->set('api_token', $token);

                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Sessão expirada. Faça login novamente.',
        ], 401);
    }
}
