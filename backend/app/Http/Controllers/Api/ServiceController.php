<?php

namespace App\Http\Controllers\Api;// istersen App\Http\Controllers\Api da yapabilirsin
use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
/**
 * @OA\Tag(
 *     name="Services",
 *     description="Hizmetler ile ilgili işlemler"
 * )
 */
class ServiceController extends Controller
{
    // Hizmet listeleme
     /**
     * @OA\Get(
     *     path="/api/services",
     *     summary="Hizmet listesini getirir",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Hizmet adı ile arama",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hizmet listesi",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string", nullable=true),
     *                 @OA\Property(property="cost", type="number", format="float", nullable=true)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%');
        }

        $service = $query->orderBy('name', 'asc')->get();

        return response()->json($service);
    }

    // Tek Hizmet görüntüleme
    /**
     * @OA\Get(
     *     path="/api/services/{service}",
     *     summary="Tek hizmeti getirir",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="service",
     *         in="path",
     *         description="Hizmet ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hizmet detayları",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="cost", type="number", format="float", nullable=true)
     *         )
     *     )
     * )
     */
    public function show(Service $service)
    {
        return response()->json($service);
    }

    // Hizmet ekleme
     /**
     * @OA\Post(
     *     path="/api/services",
     *     summary="Yeni hizmet ekler",
     *     tags={"Services"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="cost", type="number", format="float", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hizmet başarıyla eklendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="service", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string", nullable=true),
     *                 @OA\Property(property="cost", type="number", format="float", nullable=true)
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description'  => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $service = Service::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Hizmet eklendi!',
            'service' => $service
        ]);
    }

    // Hizmet güncelleme
     /**
     * @OA\Put(
     *     path="/api/services/{service}",
     *     summary="Hizmeti günceller",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="service",
     *         in="path",
     *         description="Hizmet ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="cost", type="number", format="float", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hizmet başarıyla güncellendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="service", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string", nullable=true),
     *                 @OA\Property(property="cost", type="number", format="float", nullable=true)
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description'  => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',

        ]);

        $service->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Hizmet güncellendi!',
            'service' => $service
        ]);
    }

    // Hizmet silme
     /**
     * @OA\Delete(
     *     path="/api/services/{service}",
     *     summary="Hizmeti siler",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="service",
     *         in="path",
     *         description="Hizmet ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hizmet başarıyla silindi",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Silme sırasında hata oluştu",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Service $service)
    {
        try {
            $service->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Hizmet silindi!'
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Silme sırasında hata oluştu!'
            ], 500);
        }
    }
}
