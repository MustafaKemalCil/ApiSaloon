<!DOCTYPE html>
<html lang="tr">
<head>
    @php
    $user = auth()->user();
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex h-screen bg-gray-100">

    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md flex flex-col">
        <div class="p-4 text-xl font-bold border-b">Admin Panel</div>
        <nav class="mt-4 flex-1">
            <a href="{{ route('dashboard') }}" class="block py-2 px-4 hover:bg-gray-200">Dashboard</a>
            <a href="{{ route('customers.index') }}" class="block py-2 px-4 hover:bg-gray-200">Müşteriler</a>
            <a href="{{ route('appointments.index') }}" class="block py-2 px-4 hover:bg-gray-200">randevu</a>
            @if($user->position === 'Admin' || $user->position === 'Manager')
            <a href="{{ route('employees.index') }}" class="block py-2 px-4 hover:bg-gray-200">calısan</a>
            @endif

            <a href="{{ route('profile') }}" class="block py-2 px-4 hover:bg-gray-200">Profil</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left py-2 px-4 hover:bg-gray-200">Çıkış Yap</button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
         <div class="flex-1 flex flex-col">

                    <!-- Top Bar -->
          <header class="bg-white shadow-md flex justify-between items-center px-6 h-16">
                        <div class="text-lg font-semibold">Dashboard</div>

                        <!-- Profile -->
                        <div class="relative flex items-center space-x-2">
                            <span class="text-gray-700"> {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>

                            <!-- Profile Icon -->
                         <button id="profileBtn" type="button"
    class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-700 font-bold hover:scale-110 transition">
    P
</button>


                <!-- Dropdown Menu -->
                    <div id="profileMenu"
                        class="hidden absolute right-0 top-14 w-48 bg-white shadow-lg rounded-lg border border-gray-200">

                        <!-- Kullanıcı Bilgisi -->
                        <div class="px-4 py-3 border-b bg-gray-50">
                            <div class="text-gray-800 font-semibold">
                                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                            </div>
                            <div class="text-gray-500 text-sm">
                                {{ auth()->user()->email }}
                            </div>
                        </div>

                        <!-- Profilim -->
                        <a href="{{ route('profile') }}"
                        class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                            Profilim
                        </a>

                        <!-- Çıkış Yap -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">
                                Çıkış Yap
                            </button>
                        </form>
                    </div>

         </header>

                <!-- Content -->
                <main class="flex-1 p-6 overflow-auto">
                    @yield('content')
                </main>

     </div>
<script>
    const btn = document.getElementById('profileBtn');
    const menu = document.getElementById('profileMenu');

    btn.addEventListener('click', () => {
        menu.classList.toggle('hidden');
    });

    document.addEventListener('click', (event) => {
        if (!btn.contains(event.target) && !menu.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });
</script>

</body>
</html>
