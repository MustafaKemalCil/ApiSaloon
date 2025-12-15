<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Customer; // Modeli ekledik
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // Müşteri listeleme
    public function index(Request $request)
    {
        $query = Customer::query(); // Model üzerinden sorgu başlat

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('first_name', 'like', $search . '%');
        }

        $customers = $query->orderBy('created_at', 'desc')->get();

        return view('customers.index', compact('customers'));
    }

    // Yeni müşteri formu + müşteri listesi
    public function create()
    {
        $customers = Customer::orderBy('created_at', 'desc')->get();

        return view('customers.create', compact('customers'));
    }

    // Müşteri ekleme
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'gender'     => 'required|in:male,female',
            'phone'      => 'nullable|string|max:20',
            'email'      => 'nullable|email|unique:customers,email',
            'note'       => 'nullable|string|max:1000',
        ]);

        Customer::create($request->only([
            'first_name', 'last_name', 'gender', 'phone', 'email', 'note'
        ]));

        return redirect()->route('customers.index')->with('success', 'Müşteri eklendi!');
    }

    // Müşteri düzenleme formu
    public function edit(Customer $customer) // Route model binding kullan
    {
        return view('customers.edit', compact('customer'));
    }

    // Müşteri güncelleme
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'gender'     => 'nullable|string|max:20',
            'phone'      => 'nullable|string|max:50',
            'email'      => "nullable|email|unique:customers,email,{$id}",
            'note'       => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Müşteri güncellendi',
            'customer' => $customer
        ]);
    }

    // Müşteri silme
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Müşteri silindi!'
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
