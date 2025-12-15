<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Laravel')</title>
 <body class="bg-gray-100 min-h-screen">

    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- Ana içerik -->
    <div class="container mx-auto p-8">
        @yield('content')
    </div>

</body>
</html>
