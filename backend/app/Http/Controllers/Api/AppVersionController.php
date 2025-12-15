<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\AppVersion;
use App\Http\Controllers\Controller; 
class AppVersionController extends Controller
{
    /**
     * Flutter uygulamasına en son sürümü döndüren endpoint
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
