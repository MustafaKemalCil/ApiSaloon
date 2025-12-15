@extends('authlayout.navbar')

@section('content')
<div class="max-w-md mx-auto mt-12 bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Profil Bilgileriniz</h2>

    <div class="space-y-4">
        <div class="flex justify-between items-center bg-gray-100 rounded p-4">
            <span class="font-semibold text-gray-700">Ad / Kullanıcı Adı:</span>
            <span class="text-gray-900">{{ session('user_name') }}</span>
        </div>

        <div class="flex justify-between items-center bg-gray-100 rounded p-4">
            <span class="font-semibold text-gray-700">E-posta:</span>
            <span class="text-gray-900">{{ $email }}</span>
        </div>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('dashboard') }}"
           class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded shadow">
           Dashboard’a Dön
        </a>
    </div>
</div>
@endsection
