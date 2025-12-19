<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use Carbon\Carbon;
/**
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Dashboard verileri için API"
 * )
 */
class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/dashboard",
     *     summary="Dashboard verilerini getirir",
     *     tags={"Dashboard"},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Seçili tarih (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="view",
     *         in="query",
     *         description="Görünüm tipi: daily veya weekly",
     *         required=false,
     *         @OA\Schema(type="string", default="daily")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard verileri",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="users",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="first_name", type="string"),
     *                     @OA\Property(property="last_name", type="string")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="appointments",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="user_id", type="integer"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="cost", type="number", format="float"),
     *                     @OA\Property(
     *                         property="customer",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="first_name", type="string"),
     *                         @OA\Property(property="last_name", type="string")
     *                     ),
     *                     @OA\Property(property="start_datetime", type="string", format="date-time"),
     *                     @OA\Property(property="end_datetime", type="string", format="date-time", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(property="timeSlots", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="today", type="string", format="date"),
     *             @OA\Property(property="viewType", type="string"),
     *             @OA\Property(property="showDaily", type="boolean"),
     *             @OA\Property(property="daysOfWeek", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="startOfWeek", type="string", format="date")
     *         )
     *     )
     * )
     */
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
