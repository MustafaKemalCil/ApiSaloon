<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\AppVersion;
use App\Http\Controllers\Controller; 

/**
 * @OA\Tag(
 *     name="App Versions",
 *     description="Flutter uygulama sürümleri ile ilgili işlemler"
 * )
 */
class AppVersionController extends Controller
{
    /**
     * Flutter uygulamasına en son sürümü döndüren endpoint
     */
     /**
     * @OA\Post(
     *     path="/api/app-version/latest",
     *     summary="Flutter uygulamasına en son sürümü döndürür",
     *     tags={"App Versions"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="platform", type="string", example="android", description="android veya ios"),
     *             @OA\Property(property="version", type="string", example="1.0.0", description="Mevcut kullanıcı sürümü")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="En son sürüm veya zaten en güncel sürüm",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You are using the latest version"),
     *             @OA\Property(property="version", type="string", example="2.0.0"),
     *             @OA\Property(property="url", type="string", example="https://example.com/app.apk")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Sürüm bulunamadı",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No version found")
     *         )
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
   public function latest(Request $request)
{
    // POST body’den platform al, default android
    $platform = $request->input('platform', 'android');
    $userversion = $request->input('version', null);
    
    $latest = AppVersion::where('platform', $platform)
        ->orderBy('created_at', 'desc')
        ->first();

    if (!$latest) {
        return response()->json(['message' => 'No version found'], 404);
    }
    if($latest->version == $userversion){
        return response()->json(['message' => 'You are using the latest version'], 200);
    }
    return response()->json([
        'version' => $latest->version,
        'url' => $latest->file_path,
    ]);
}

    /**
     * Yeni sürüm ekleme (dosya yükleme yerine string path)
     */
    /**
     * @OA\Post(
     *     path="/api/app-version",
     *     summary="Yeni sürüm ekle",
     *     tags={"App Versions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"platform","version","file_path"},
     *             @OA\Property(property="platform", type="string", example="android"),
     *             @OA\Property(property="version", type="string", example="2.0.0"),
     *             @OA\Property(property="file_path", type="string", example="https://example.com/app.apk")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Yeni sürüm oluşturuldu",
     *         @OA\JsonContent(
     *             @OA\Property(property="platform", type="string"),
     *             @OA\Property(property="version", type="string"),
     *             @OA\Property(property="file_path", type="string"),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string")
     *         )
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'platform' => 'required|string',     // android / ios
            'version' => 'required|string',      // 2.0.0 vb
            'file_path' => 'required|string',    // APK/IPA URL veya path
        ]);

        $appVersion = AppVersion::create([
            'platform' => $request->platform,
            'version' => $request->version,
            'file_path' => $request->file_path, // string olarak kaydedilecek
        ]);

        return response()->json($appVersion, 201);
    }
}
