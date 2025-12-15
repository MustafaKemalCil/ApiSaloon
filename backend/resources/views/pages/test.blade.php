<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>MongoDB Test Sayfası</title>
</head>
<body>
 @include('layouts.navbar')

<h2>MongoDB Kayıt Test</h2>

<form action="{{ route('test.form') }}" method="POST">
    @csrf
    <label>Ad Gir:</label>
    <input type="text" name="name" required>
    <button type="submit">Kaydet</button>
</form>

<hr>

<h3>Mevcut Kayıtlar</h3>
<ul>
    @forelse($records as $record)
        <li>{{ $record['name'] }}</li>
    @empty
        <li>Henüz kayıt yok</li>
    @endforelse
</ul>

</body>
</html>
