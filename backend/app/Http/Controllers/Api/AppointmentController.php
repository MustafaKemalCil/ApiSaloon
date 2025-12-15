<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{

    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
    /**
     * Randevu listesini getir (daily veya weekly)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $selectedDate = $request->get('date');
        $today = $selectedDate ?? Carbon::today()->format('Y-m-d');

        $viewType = $request->get('view', 'daily'); // default daily

        $customers = Customer::all();
        $users     = User::all();

        // Randevuları al
        if ($user->position === 'Admin' || $user->position === 'Manager') {
            $appointments = Appointment::with(['customer', 'user'])
                ->orderBy('start_datetime')
                ->get();
        } else {
            $appointments = Appointment::with(['customer', 'user'])
                ->where('user_id', $user->id)
                ->orderBy('start_datetime')
                ->get();
        }

        // Saat dilimleri (8:00 - 20:00 arası yarım saatlik)
        $timeSlots = [];
        for ($h = 8; $h < 20; $h++) {
            $timeSlots[] = sprintf("%02d:00", $h);
            $timeSlots[] = sprintf("%02d:30", $h);
        }

        // JSON ile döndür
        return response()->json([
            'customers'    => $customers,
            'users'        => $users,
            'appointments' => $appointments,
            'timeSlots'    => $timeSlots,
            'today'        => $today,
            'viewType'     => $viewType,
        ]);
    }

    /**
     * Yeni randevu oluştur
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'employee_id'    => 'required|exists:users,id',
            'start_datetime' => 'required|date',
            'end_datetime'   => 'nullable|date|after_or_equal:start_datetime',
            'note'           => 'nullable|string',
            'service'           => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $start = Carbon::parse($request->start_datetime)->format('Y-m-d H:i:s');
        $end   = $request->end_datetime ? Carbon::parse($request->end_datetime)->format('Y-m-d H:i:s') : null;

        $appointment = Appointment::create([
            'customer_id'    => $request->customer_id,
            'user_id'        => $request->employee_id,
            'service'          => $request->service,
            'cost'          => $request->cost,
            'start_datetime' => $start,
            'end_datetime'   => $end,
            'note'           => $request->note,
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Randevu başarıyla eklendi.',
            'appointment' => $appointment->load('customer', 'user')
        ]);
    }

    /**
     * Belirli randevuyu getir
     */
    public function show($id)
    {
        $appointment = Appointment::with(['customer', 'user'])->findOrFail($id);
        return response()->json($appointment);
    }

     /**
     * Çalışan idye göre çek
     */
    public function showByCustomer($customerId)
    {
        $appointments = Appointment::with(['customer', 'user'])
                            ->where('customer_id', $customerId)
                            ->orderBy('start_datetime', 'desc')
                            ->get();

        if ($appointments->isEmpty()) {
            return response()->json([
                'message' => 'Bu müşteriye ait randevu bulunamadı'
            ], 404);
        }

        return response()->json($appointments);
    }
    /**
     * Randevuyu güncelle
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate([
            'customer_id'    => 'sometimes|exists:customers,id',
            'employee_id'    => 'sometimes|exists:users,id',
            'start_datetime' => 'sometimes|date',
            'end_datetime'   => 'nullable|date|after_or_equal:start_datetime',
            'service'           => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'note'           => 'nullable|string',
            'status' => 'nullable|string',

        ]);

        if ($request->has('customer_id')) {
            $appointment->customer_id = $request->customer_id;
        }
        if ($request->has('employee_id')) {
            $appointment->user_id = $request->employee_id;
        }
        if ($request->has('start_datetime')) {
            $appointment->start_datetime = Carbon::parse($request->start_datetime);
        }
        if ($request->has('end_datetime')) {
            $appointment->end_datetime = Carbon::parse($request->end_datetime);
        }
        if ($request->has('service')) {
            $appointment->service = $request->service;
        }
        if ($request->has('cost')) {
            $appointment->cost = $request->cost;
        }
        if ($request->has('note')) {
            $appointment->note = $request->note;
        }
        if ($request->has('status')) {
            $appointment->status = $request->status;
        }
        $appointment->save();

        return response()->json([
            'success'     => true,
            'message'     => 'Randevu güncellendi.',
            'appointment' => $appointment->load('customer', 'user')
        ]);
    }

    /**
     * Randevuyu sil
     */
    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Randevu silindi.'
        ]);
    }
}
