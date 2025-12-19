<?php

namespace App\Http\Controllers\Api;// istersen App\Http\Controllers\Api da yapabilirsin
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
 
class CustomerController extends Controller
{
     
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('first_name', 'like', $search . '%');
        }

        $customers = $query->orderBy('created_at', 'desc')->get();

        return response()->json($customers);
    }


    
    public function show(Customer $customer)
    {
            // appointments ile ilişkili user ve payments bilgilerini de yükle
            $customer->load('appointments.user', 'appointments.payments');

            // appointments'ı map ile user adını ve ödenen toplamı ekleyelim
        $appointments = $customer->appointments->map(function ($appt) {
            return [
                'id' => $appt->id,
                'start_datetime' => $appt->start_datetime,
                'end_datetime' => $appt->end_datetime,
                'service' => $appt->service,
                'cost' => $appt->cost,
                'note' => $appt->note,
                'status' => $appt->status,
                'worker_name' => $appt->user ? $appt->user->first_name . ' ' . $appt->user->last_name : null,
                'total_payment' => $appt->payments->sum('amount'),
                'payments' => $appt->payments->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'amount' => $p->amount,
                        'method' => $p->method,
                        'note' => $p->note,
                        'created_at' => $p->created_at,
                    ];
                }),
            ];
        });




            return response()->json([
                'customer' => $customer,
                'appointments' => $appointments 
            ]);
    }

    // Müşteri ekleme
     
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'gender'     => 'required|in:male,female',
            'phone'      => 'nullable|string|max:20',
            'email'      => 'nullable|email|unique:customers,email',
            'note'       => 'nullable|string|max:1000',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Müşteri eklendi!',
            'customer' => $customer
        ]);
    }

    
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'gender'     => 'nullable|string|max:20',
            'phone'      => 'nullable|string|max:50',
            'email'      => "nullable|email|unique:customers,email,{$customer->id}",
            'note'       => 'nullable|string',
        ]);

        $customer->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Müşteri güncellendi!',
            'customer' => $customer
        ]);
    }

     
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
