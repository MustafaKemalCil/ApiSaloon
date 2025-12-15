@extends('authlayout.navbar')

@section('content')
<div class="container mx-auto max-w-2xl mt-10 p-6 bg-white shadow rounded">

    <h1 class="text-2xl font-bold mb-4">👤 Profil Bilgileri</h1>

    @if(session('success'))
        <div class="bg-green-200 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="font-semibold">Ad</label>
            <input type="text" name="first_name" value="{{ $user->first_name }}"
                   class="w-full p-2 border rounded mt-1">
        </div>

        <div class="mb-4">
            <label class="font-semibold">Soyad</label>
            <input type="text" name="last_name" value="{{ $user->last_name }}"
                   class="w-full p-2 border rounded mt-1">
        </div>

        <div class="mb-4">
            <label class="font-semibold">Email</label>
            <input type="email" name="email" value="{{ $user->email }}"
                   class="w-full p-2 border rounded mt-1">
        </div>

        <div class="mb-4">
            <label class="font-semibold">Telefon</label>
            <input type="text" name="phone" value="{{ $user->phone }}"
                   class="w-full p-2 border rounded mt-1">
        </div>

        <div class="text-right">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                Kaydet
            </button>
        </div>

    </form>
    <hr class="my-6">

<h2 class="text-xl font-bold mb-4">🔐 Şifre Değiştir</h2>

<form action="{{ route('profile.updatePassword') }}" method="POST">
    @csrf

    <div class="mb-4">
        <label class="font-semibold">Mevcut Şifre</label>
        <input type="password" name="current_password"
               class="w-full p-2 border rounded mt-1" required>
    </div>

    <div class="mb-4">
        <label class="font-semibold">Yeni Şifre</label>
        <input type="password" name="new_password"
               class="w-full p-2 border rounded mt-1" required>
    </div>

    <div class="mb-4">
        <label class="font-semibold">Yeni Şifre (Tekrar)</label>
        <input type="password" name="new_password_confirmation"
               class="w-full p-2 border rounded mt-1" required>
    </div>

    <div class="text-right">
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">
            Şifreyi Güncelle
        </button>
    </div>
</form>

</div>
@endsection
