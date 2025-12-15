<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Laravel')</title>
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
      crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<body class="min-h-screen bg-gray-100">

    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- Header (sayfaya özel ya da genel) -->
    @include('layouts.header')
     @include('layouts.about')
      @include('layouts.map')
     @include('layouts.intro')
     @include('layouts.footer')
    <!-- Ana içerik -->
    <div class="container p-8 mx-auto">
        @yield('content')
    </div>

</body>
</html>
