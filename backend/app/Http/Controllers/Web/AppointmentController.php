<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use HasFactory;
class AppointmentController extends Controller
{







        public function index(Request $request)
        {
            $user = auth()->user(); // Giriş yapan kullanıcı
            // Kullanıcının seçtiği tarih varsa al, yoksa bugünü kullan
            $selectedDate = $request->get('date'); // Formdan gelen tarih
            $today = $selectedDate ?? Carbon::today()->format('Y-m-d');


            // Görünüm tipi: daily veya weekly
            $viewType = $request->get('view', 'daily'); // default daily

            // Tüm müşteriler ve çalışanlar
                $customers = Customer::all();
                $users = User::all();

            // Randevuları, ilişkili müşteri ve çalışan bilgisiyle al
            if ($user->position === 'Admin' || $user->position === 'Manager') {
                // Admin ve müdür tüm randevuları görebiliyor
                $appointments = Appointment::with(['customer','user',])
                    ->orderBy('start_datetime')
                    ->get();
            } else {
            // Normal çalışan sadece kendi randevularını görebilir
            $appointments = Appointment::with(['customer','user'])
                ->where('user_id', $user->id)
                ->orderBy('start_datetime')
                ->get();
            }
            // Saat dilimleri (8:00 - 20:00 arası yarım saatlik slotlar)
            $timeSlots = [];
            for ($h = 8; $h < 20; $h++) {
                $timeSlots[] = sprintf("%02d:00", $h);
                $timeSlots[] = sprintf("%02d:30", $h);
            }


            // Görünüm tipi: daily veya weekly
            $viewType = $request->get('view', 'daily'); // default daily
            $showDaily = $viewType === 'daily' && $selectedDate ? true : false;

            return view('appointments.index', compact('users','customers', 'appointments', 'timeSlots', 'today', 'showDaily', 'viewType'));
        }



        public function store(Request $request)
        {
            // Validasyon
            $request->validate([
                'customer_id'    => 'required|exists:customers,id',  // SQL Server id kullan
                'employee_id'    => 'required|exists:users,id',      // formdan gelen employee_id
                'start_datetime' => 'required|date',
                'end_datetime'   => 'nullable|date|after_or_equal:start_datetime',
                'title'          => 'nullable|string|max:255',
                'note'           => 'nullable|string',
            ]);

            // Tarihleri formatla
            $start = Carbon::parse($request->start_datetime)->format('Y-m-d H:i:s');
            $end   = $request->end_datetime ? Carbon::parse($request->end_datetime)->format('Y-m-d H:i:s') : null;

            // Randevu ekle
            Appointment::create([
                'customer_id'    => $request->customer_id,
                'user_id'        => $request->employee_id, // burada employee_id kullanılıyor
                'title'          => $request->title,
                'start_datetime' => $start,
                'end_datetime'   => $end,
                'note'           => $request->note,
            ]);

            return redirect()->back()->with('success', 'Randevu başarıyla eklendi.');




        }



}
