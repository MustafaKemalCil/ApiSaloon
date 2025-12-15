<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); // API auth kullanıyorsan: sanctum veya token

        $selectedDate = $request->get('date');
        $today = $selectedDate ?? Carbon::today()->format('Y-m-d');

        $viewType = $request->get('view', 'daily');

        // Çalışanlar
        $users = User::all();

        // Randevular
        $appointmentsQuery = Appointment::with(['customer', 'user'])
            ->orderBy('start_datetime');

        if (!in_array($user->position, ['Admin', 'Manager'])) {
            $appointmentsQuery->where('user_id', $user->id);
        }

        $appointments = $appointmentsQuery->get()->map(function($a) {
            return [
                'id' => $a->id,
                'user_id' => $a->user_id,
                'status' => $a->status,
                'cost' => $a->cost,
                'customer' => [
                    'id' => $a->customer->id,
                    'first_name' => $a->customer->first_name,
                    'last_name' => $a->customer->last_name,
                ],
                'start_datetime' => $a->start_datetime,
                'end_datetime' => $a->end_datetime,
            ];
        });

        // Saat dilimleri
        $timeSlots = [];
        for ($h = 8; $h < 20; $h++) {
            $timeSlots[] = sprintf("%02d:00", $h);
            $timeSlots[] = sprintf("%02d:30", $h);
        }

        // Haftanın günleri
        $startOfWeek = Carbon::parse($today)->startOfWeek()->format('Y-m-d');
        $daysOfWeek = ['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'];

        return response()->json([
            'users' => $users->map(function($u) {
                return [
                    'id' => $u->id,
                    'first_name' => $u->first_name,
                    'last_name' => $u->last_name,
                ];
            }),
            'appointments' => $appointments,
            'timeSlots' => $timeSlots,
            'today' => $today,
            'viewType' => $viewType,
            'showDaily' => $viewType === 'daily',
            'daysOfWeek' => $daysOfWeek,
            'startOfWeek' => $startOfWeek,
        ]);
    }
}
