<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // Tüm ödemeleri listele
    public function index(Appointment $appointment)
    {
        $appointment->load('payments');

        return response()->json([
            'payments' => $appointment->payments,
            'total_paid' => $appointment->payments->sum('amount'),
            'remaining' => $appointment->cost - $appointment->payments->sum('amount'),
            'is_paid' => ($appointment->cost - $appointment->payments->sum('amount')) <= 0
        ]);
    }

    // Yeni ödeme ekle
    public function store(Request $request, Appointment $appointment)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'note'   => 'nullable|string',
            'customer_id' => 'required|exists:customers,id', // zorunlu ve DB'de var mı kontrolü

        ]);

        $paid = $appointment->payments()->sum('amount');
        $remaining = $appointment->cost - $paid;

        if ($request->amount > $remaining) {
            return response()->json([
                'error' => 'Ödeme randevu ücretini aşamaz. Kalan: ' . $remaining . ' TL'
            ], 422);
        }
        
        $payment = $appointment->payments()->create([
            'amount' => $request->amount,
            'note'   => $request->note,
             'customer_id' => $request->customer_id,
        ]);
         if ($paid+$request->amount >= $appointment->cost) {
        $appointment->update(['status' => 'paid']);
        }
        return response()->json([
            'message' => 'Ödeme başarıyla kaydedildi.',
            'payment' => $payment
        ]);
    }
}
