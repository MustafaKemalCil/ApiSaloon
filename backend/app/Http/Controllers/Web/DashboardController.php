<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user(); // Giriş yapan kullanıcı
        $selectedDate = $request->get('date');
        $today = $selectedDate ?? Carbon::today()->format('Y-m-d');

        $viewType = $request->get('view', 'daily');

        // Çalışanları çek
        $users = User::all();

        // Randevuları al
        if ($user->position === 'Admin' || $user->position === 'Manager') {
            $appointments = Appointment::with(['customer','user'])
                ->orderBy('start_datetime')
                ->get();
        } else {
            $appointments = Appointment::with(['customer','user'])
                ->where('user_id', $user->id)
                ->orderBy('start_datetime')
                ->get();
        }

        // Saat dilimleri
        $timeSlots = [];
        for ($h = 8; $h < 20; $h++) {
            $timeSlots[] = sprintf("%02d:00", $h);
            $timeSlots[] = sprintf("%02d:30", $h);
        }

        // Haftanın günleri
        $startOfWeek = Carbon::parse($today)->startOfWeek();
        $daysOfWeek = ['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'];

        // JSON olarak döndür
        return response()->json([
            'users' => $users,
            'appointments' => $appointments,
            'timeSlots' => $timeSlots,
            'today' => $today,
            'showDaily' => $viewType === 'daily',
            'viewType' => $viewType,
            'daysOfWeek' => $daysOfWeek,
            'startOfWeek' => $startOfWeek,
        ]);
    }
}
