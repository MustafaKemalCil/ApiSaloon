    @extends('authlayout.navbar'){{-- kendi layout dosyana göre değiştir --}}
    @section('content')
   <!-- Butonlar -->
        <div class="mb-4 space-x-2">
            <button id="showDiv1" class="px-4 py-2 bg-blue-500 text-white rounded">Haftalık Görünüm</button>
            <button id="showDiv2" class="px-4 py-2 bg-green-500 text-white rounded">Günlük Görünüm</button>
        </div>

    {{-- Haftalık Görünüm --}}
         @php
            use Carbon\Carbon;

            // Kullanıcının seçtiği tarih
            $selectedDate = request('date') ? Carbon::parse(request('date')) : now();

            // Haftanın başlangıcı (Pazartesi)
            $startOfWeek = $selectedDate->copy()->startOfWeek();

            $daysOfWeek = ['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'];

            $timeSlots = [];
            for($h=8; $h<20; $h++){
                $timeSlots[] = sprintf("%02d:00", $h);
                $timeSlots[] = sprintf("%02d:30", $h);
            }

            $colors = ['bg-blue-200','bg-green-200','bg-yellow-200','bg-pink-200','bg-purple-200','bg-orange-200'];
            $bgColors = ['#BFDBFE','#86EFAC','#FEF08A','#FBCFE8','#DDD6FE','#FDBA74'];
         @endphp


        <div id="div1" @if($showDaily) style="display: none;" @endif class="overflow-auto p-4">

            <form method="GET" action="{{ url()->current() }}">
                <input type="hidden" name="view" value="view">
                <label for="date" class="mr-2 font-semibold">Tarih Seç:</label>
                <input type="date" name="date" id="date"
                    value="{{ request('date', now()->format('Y-m-d')) }}"
                    class="border p-1 rounded">
                <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded ml-2">Göster</button>
            </form>


            <table class="table-fixed border-collapse border border-gray-300 w-full text-center">

                <thead>
                    <tr>
                        <th class="border p-2 bg-gray-200" >Saat</th>
                        @foreach($daysOfWeek as $i => $dayName)
                            @php $day = $startOfWeek->copy()->addDays($i); @endphp
                            <th class="border p-2 bg-gray-200" colspan="{{ $users->count() }}">
                                {{ $day->format('d-m-Y') }} ({{ $dayName }})
                            </th>
                        @endforeach
                    </tr>
                    <tr>
                        <th class="border p-2 bg-gray-100" ></th>
                        @foreach($daysOfWeek as $i => $dayName)
                            @foreach($users as $index => $user)
                                <th class="border p-2 bg-gray-100 w-[120px] ">{{ $user->first_name }} {{ $user->last_name }}</th>
                            @endforeach
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeSlots as $slot)
                        <tr>
                            <td class="border p-2" >{{ $slot }}</td>
                                @foreach($daysOfWeek as $i => $dayName)
                                    @php $day = $startOfWeek->copy()->addDays($i); @endphp
                                    @foreach($users as $index => $user)
                                        @php
                                            $slotStart = Carbon::parse($day->format('Y-m-d').' '.$slot);
                                            $slotEnd = $slotStart->copy()->addMinutes(30);

                                            $dayAppointments = $appointments->filter(function($a) use ($day){
                                                return Carbon::parse($a->start_datetime)->format('Y-m-d') === $day->format('Y-m-d');
                                            });

                                            $appointment = $dayAppointments->first(function($a) use ($user, $slotStart, $slotEnd){
                                                $start = Carbon::parse($a->start_datetime);
                                                $end = Carbon::parse($a->end_datetime);
                                                return $a->user_id == $user->id && $start->lt($slotEnd) && $end->gt($slotStart);
                                            });

                                            $cellColor = $appointment ? $colors[$index % count($colors)] : 'bg-gray-100';
                                        @endphp
                                        <td class="border p-2 text-center {{ $cellColor }}">
                                            @if($appointment)
                                                {{-- Müşteri adı (maks. 10 karakter) --}}
                                                <div class="text-xs font-semibold break-all">
                                                    {{ \Illuminate\Support\Str::limit($appointment->customer->first_name ?? '-', 10) }}
                                                </div>

                                                {{-- Randevu başlığı (maks. 20 karakter, 10’dan sonra alt satıra geçsin) --}}
                                                <div class="text-xs text-gray-700 break-words max-w-[80px] leading-tight">
                                                    {{ \Illuminate\Support\Str::limit($appointment->title ?? '_',10) }}
                                                </div>
                                                {{-- Randevu açıklaması (maks. 20 karakter, 10 karakterden sonra alt satıra geçsin) --}}
                                                <div class="text-xs text-gray-700 break-words max-w-[100px] leading-tight">
                                                    {{ \Illuminate\Support\Str::limit($appointment->note ?? '', 20) }}
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    {{-- Çalışan Bazlı Randevu Takvimi --}}

    <div id="div2" @if(!$showDaily) style="display: none;" @endif class="overflow-x-auto">
            <form method="GET" action="{{ url()->current() }}">
                <input type="hidden" name="view" value="daily">
                <label for="date" class="mr-2 font-semibold">Tarih Seç:</label>
                <input type="date" name="date" id="date"
                    value="{{ request('date', now()->format('Y-m-d')) }}"
                    class="border p-1 rounded">
                <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded ml-2">Göster</button>
            </form>

         <table class="min-w-full border border-gray-300 text-sm">
            <thead>
                <tr>
                    <th class="border p-2 w-16 text-center">Saat</th>
                    @foreach($users as $index => $user)
                        @php
                            $bgColor = $bgColors[$index % count($bgColors)];
                        @endphp
                        <th class="border p-2 text-center" style="background-color: {{ $bgColor }};">
                            {{ $user->first_name }} {{ $user->last_name }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($timeSlots as $slot)
                    <tr>
                        <td class="border p-1 text-center font-semibold">{{ $slot }}</td>
                        @foreach($users as $index => $employee)
                            @php
                                $slotStart = \Carbon\Carbon::parse($today . ' ' . $slot);
                                $slotEnd = $slotStart->copy()->addMinutes(30);

                                $appointment = $appointments->first(function($a) use ($slotStart, $slotEnd, $employee) {
                                    $s = \Carbon\Carbon::parse($a->start_datetime);
                                    $e = \Carbon\Carbon::parse($a->end_datetime);
                                    return intval($a->user_id) === intval($employee->id)
            && ($s->lt($slotEnd) && $e->gt($slotStart));
                                        });

                                        $cellColor = $appointment ? $bgColors[$index % count($bgColors)] : '#F3F4F6'; // bg-gray-100 yerine hex
                                    @endphp
                                    <td class="border p-1 text-xs" style="background-color: {{ $cellColor }};">
                                        @if($appointment)
                                            <div class="font-semibold text-gray-800">
                                                {{ $appointment->customer->first_name ?? '-' }}
                                            </div>
                                            <div class="text-gray-600">
                                                {{ $appointment->title ?? '' }}
                                            </div>
                                            <div class="text-gray-500">
                                                {{ \Carbon\Carbon::parse($appointment->start_datetime)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($appointment->end_datetime)->format('H:i') }}
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
         </table>
    </div>


  <script>
    const div1 = document.getElementById('div1');
    const div2 = document.getElementById('div2');

    document.getElementById('showDiv1').addEventListener('click', () => {
        div1.style.display = 'block';   // Haftalık görünümü göster
        div2.style.display = 'none';    // Günlük görünümü gizle
    });

    document.getElementById('showDiv2').addEventListener('click', () => {
        div1.style.display = 'none';    // Haftalık görünümü gizle
        div2.style.display = 'block';   // Günlük görünümü göster
    });
</script>

    @endsection
