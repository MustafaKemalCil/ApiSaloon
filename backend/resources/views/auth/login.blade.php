    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Giriş Yap</title>
        @vite('resources/css/app.css')
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen">

        <div class="bg-white shadow-lg rounded-xl p-10 w-full max-w-md text-center">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800">Giriş Yap</h2>

            @if ($errors->any())
                <div class="mb-3 text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <input type="email" name="email" placeholder="E-posta"
                    class="mx-auto block w-80 px-4 py-3 border text-base rounded-lg
                        focus:ring focus:ring-blue-200 focus:outline-none"
                    required>

                <input type="password" name="password" placeholder="Şifre"
                    class="mx-auto block w-80 px-4 py-3 border text-base rounded-lg
                        focus:ring focus:ring-blue-200 focus:outline-none"
                    required>

                <button type="submit"
                    class="mx-auto block w-80 bg-blue-600 text-white py-3 text-base rounded-lg
                        hover:bg-blue-700 transition font-medium">
                    Giriş Yap
                </button>
            </form>


        </div>

    </body>
    </html>
