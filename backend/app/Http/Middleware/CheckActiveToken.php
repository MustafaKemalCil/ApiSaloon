<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class CheckActiveToken
{
    public function handle(Request $request, Closure $next)
{
    $authHeader = $request->header('Authorization') ?? $request->header('authorization');

    if (!$authHeader) {
        return response()->json(['message' => 'Token bulunamadı'], 401);
    }

    $authHeader = trim($authHeader);

    if (!str_starts_with($authHeader, 'Bearer ')) {
        return response()->json(['message' => 'Token bulunamadı'], 401);
    }

    $tokenString = trim(substr($authHeader, 7));

    // Eğer token "1|xxxx" formatındaysa pipe sonrası alın
    if (str_contains($tokenString, '|')) {
        $parts = explode('|', $tokenString, 2);
        $tokenString = $parts[1] ?? null;
        if (!$tokenString) {
            return response()->json(['message' => 'Token bulunamadı'], 401);
        }
    }

    $token = PersonalAccessToken::where('token', hash('sha256', $tokenString))
        ->where('is_active', true)
        ->first();

    if (!$token) {
        return response()->json(['message' => 'Token geçersiz veya pasif'], 401);
    }

    $request->setUserResolver(fn () => $token->tokenable);

    return $next($request);
}


}
