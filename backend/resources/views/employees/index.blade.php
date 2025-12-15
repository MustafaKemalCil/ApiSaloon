@extends('authlayout.navbar')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Çalışanlar</h1>

    @if(session('success'))
        <div class="bg-green-200 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Yeni Çalışan Butonu --}}
    <button id="toggleFormBtn" class="bg-blue-600 text-white px-4 py-2 rounded mb-4">
        Yeni Çalışan Ekle
    </button>

    {{-- Yeni Çalışan Formu (Başlangıçta gizli) --}}
<div id="employeeFormCard" class="bg-white p-4 rounded shadow mb-6 hidden">
    <h2 class="text-xl font-semibold mb-2">Yeni Çalışan Ekle</h2>
    <form id="employeeForm" method="POST" action="{{ route('employees.store') }}">
        @csrf

        <div>
            <label class="block font-semibold">Ad</label>
            <input type="text" name="first_name" class="border rounded p-2 w-full" required>
        </div>

        <div>
            <label class="block font-semibold">Soyad</label>
            <input type="text" name="last_name" class="border rounded p-2 w-full" required>
        </div>

        <div>
            <label class="block font-semibold">Email</label>
            <input type="email" name="email" class="border rounded p-2 w-full" required>
        </div>

        <div>
                        <label class="block font-medium">Telefon(isteğe Bağlı)</label>
                        <input type="tel" name="phone" id="phone"
                            class="border p-2 w-full rounded"
                            placeholder="+90 555 555 55 55"
                            pattern="\+?\d{1,4}[\s-]?\d{3}[\s-]?\d{3}[\s-]?\d{2}[\s-]?\d{2}"
                            title="Telefon numarası +90 555 555 55 55 formatında olmalıdır">
                    </div>

        <div>
            <label class="block font-semibold">Şifre</label>
            <input type="password" name="password" class="border rounded p-2 w-full" required>
        </div>

        <div>
            <label class="block font-semibold">Şifre Doğrulama</label>
            <input type="password" name="password_confirmation" class="border rounded p-2 w-full" required>
        </div>

        <div>
            <label class="block font-semibold">Pozisyon</label>
                <select name="position" class="border rounded p-2 w-full">
                    <option value="Employee">Çalışan</option>
                    <option value="Manager">Müdür</option>
                </select>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Kaydet</button>
    </form>
</div>


   {{-- Düzenle Modal (Başlangıçta gizli) --}}
<div id="editModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md relative">
        <h2 class="text-xl font-semibold mb-4">Çalışan Düzenle</h2>
        <form id="editForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit_id">
            <div class="mb-2">
                <label class="block font-semibold">Ad</label>
                <input type="text" name="first_name" id="edit_first_name" class="border rounded p-2 w-full" required>
            </div>
            <div class="mb-2">
                <label class="block font-semibold">Soyad</label>
                <input type="text" name="last_name" id="edit_last_name" class="border rounded p-2 w-full" required>
            </div>
            <div class="mb-2">
                <label class="block font-semibold">Email</label>
                <input type="email" name="email" id="edit_email" class="border rounded p-2 w-full" required>
            </div>
             <div>
                        <label class="block font-medium">Telefon(isteğe Bağlı)</label>
                        <input type="tel" name="phone" id="edit_phone"
                            class="border p-2 w-full rounded"
                            placeholder="+90 555 555 55 55"
                            pattern="\+?\d{1,4}[\s-]?\d{3}[\s-]?\d{3}[\s-]?\d{2}[\s-]?\d{2}"
                            title="Telefon numarası +90 555 555 55 55 formatında olmalıdır">
                    </div>
            <div class="mb-4" >
                <label class="block font-semibold">Pozisyon</label>

                 <select name="position" id="edit_position"class="border rounded p-2 w-full">
                    <option value="Employee">Çalışan</option>
                    <option value="Manager">Müdür</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" id="cancelEdit" class="bg-gray-400 text-white px-4 py-2 rounded">İptal</button>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Güncelle</button>
            </div>
        </form>
    </div>
</div>

    {{-- Çalışan Listesi --}}
    <div class="bg-white p-4 rounded shadow">
        <h2 class="text-xl font-semibold mb-2">Çalışanlar</h2>
        <table class="min-w-full border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">Ad</th>
                    <th class="border p-2">Soyad</th>
                    <th class="border p-2">Email</th>
                    <th class="border p-2">Telefon</th>
                    <th class="border p-2">Pozisyon</th>
                    <th class="border p-2">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $employee)
                    <tr id="employee-{{ $employee->id }}">
                        <td class="border p-2">{{ $employee->first_name }}</td>
                        <td class="border p-2">{{ $employee->last_name }}</td>
                        <td class="border p-2">{{ $employee->email }}</td>
                        <td class="border p-2">{{ $employee->phone ?? '-' }}</td>
                        <td class="border p-2">{{ $employee->position ?? '-' }}</td>
                        <td class="border p-2 space-x-2">
                            <button class="bg-yellow-400 px-2 py-1 rounded text-white edit-btn"
                                data-id="{{ $employee->id }}"
                                data-first_name="{{ $employee->first_name }}"
                                data-last_name="{{ $employee->last_name }}"
                                data-email="{{ $employee->email }}"
                                data-phone="{{ $employee->phone }}"
                                data-position="{{ $employee->position }}"
                            >Düzenle</button>

                            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 px-2 py-1 rounded text-white" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    // Yeni Çalışan formu toggle
    document.getElementById('toggleFormBtn').addEventListener('click', function() {
        document.getElementById('employeeFormCard').classList.toggle('hidden');
    });

    // Düzenle butonu modal açma
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_first_name').value = button.dataset.first_name;
            document.getElementById('edit_last_name').value = button.dataset.last_name;
            document.getElementById('edit_email').value = button.dataset.email;
            document.getElementById('edit_phone').value = button.dataset.phone;
            document.getElementById('edit_position').value = button.dataset.position;

            document.getElementById('editModal').classList.remove('hidden');
        });
    });

    // İptal butonu modal kapatma
    document.getElementById('cancelEdit').addEventListener('click', () => {
        document.getElementById('editModal').classList.add('hidden');
    });

    // Modal dışına tıklayınca kapatma
    document.getElementById('editModal').addEventListener('click', (e) => {
        if(e.target.id === 'editModal') {
            document.getElementById('editModal').classList.add('hidden');
        }
    });

    // Düzenleme form submit (AJAX ile)
    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const id = document.getElementById('edit_id').value;
        const data = {
            _token: '{{ csrf_token() }}',
            first_name: document.getElementById('edit_first_name').value,
            last_name: document.getElementById('edit_last_name').value,
            email: document.getElementById('edit_email').value,
            phone: document.getElementById('edit_phone').value,
            position: document.getElementById('edit_position').value,
            _method: 'PUT'
        };

        fetch(`/employees/${id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(res => location.reload());
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function(){
        $('#phone').mask('+00 000 000 00 00'); // Örnek: +90 555 555 55 55
    });
</script>
@endsection
