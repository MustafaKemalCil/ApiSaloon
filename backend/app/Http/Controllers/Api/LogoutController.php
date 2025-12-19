<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentication işlemleri"
 * )
 */
class LogoutController extends Controller
{
    /**
     * Kullanıcı logout olur ve token pasif edilir
     *
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Kullanıcı logout olur ve token pasif edilir",
     *     tags={"Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout başarılı. Token pasif edildi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token geçersiz veya header bulunamadı",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token geçersiz veya bulunamadı.")
     *         )
     *     )
     * )
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
