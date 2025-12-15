<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class LogoutController extends Controller
{
    /**
     * Bearer token varsa DB'den bul ve pasif yap
     */
    public function logout(Request $request)
    {
        // Header'dan token al
        $plainToken = $request->bearerToken();

        if (!$plainToken) {
            return response()->json([
                'message' => 'Token header bulunamadı.'
            ], 401);
        }

        // Token DB'de var mı kontrol et
        $token = PersonalAccessToken::findToken($plainToken);

        if (!$token) {
            return response()->json([
                'message' => 'Token geçersiz veya bulunamadı.'
            ], 401);
        }

        // Token varsa pasif yap
        $token->is_active = false;
        $token->save();

        return response()->json([
            'message' => 'Logout başarılı. Token pasif edildi.'
        ], 200);
    }
}
