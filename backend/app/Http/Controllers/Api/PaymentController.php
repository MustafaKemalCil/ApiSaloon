<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
/**
 * @OA\Tag(
 *     name="Payments",
 *     description="Ödeme işlemleri"
 * )
 */
class PaymentController extends Controller
{
    // Tüm ödemeleri listele
     /**
     * @OA\Get(
     *     path="/api/appointments/{appointment}/payments",
     *     summary="Randevu ödemelerini listele",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="appointment",
     *         in="path",
     *         description="Randevu ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ödemeler başarıyla listelendi",
     *         @OA\JsonContent(
     *             @OA\Property(property="payments", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="amount", type="number", format="float"),
     *                     @OA\Property(property="note", type="string", nullable=true),
     *                     @OA\Property(property="customer_id", type="integer")
     *                 )
     *             ),
     *             @OA\Property(property="total_paid", type="number", format="float"),
     *             @OA\Property(property="remaining", type="number", format="float"),
     *             @OA\Property(property="is_paid", type="boolean")
     *         )
     *     )
     * )
     */
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
     /**
     * @OA\Post(
     *     path="/api/appointments/{appointment}/payments",
     *     summary="Randevuya yeni ödeme ekle",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="appointment",
     *         in="path",
     *         description="Randevu ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", format="float"),
     *             @OA\Property(property="note", type="string", nullable=true),
     *             @OA\Property(property="customer_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ödeme başarıyla kaydedildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="payment", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="amount", type="number", format="float"),
     *                 @OA\Property(property="note", type="string", nullable=true),
     *                 @OA\Property(property="customer_id", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Geçersiz ödeme",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
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
