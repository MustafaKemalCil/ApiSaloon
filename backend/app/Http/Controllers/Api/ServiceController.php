<?php

namespace App\Http\Controllers\Api;// istersen App\Http\Controllers\Api da yapabilirsin
use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // Hizmet listeleme
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
    public function show(Service $service)
    {
        return response()->json($service);
    }

    // Hizmet ekleme
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
