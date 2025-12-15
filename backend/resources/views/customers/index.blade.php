{{-- resources/views/customerscreate.blade.php --}}

@extends('authlayout.navbar')

@section('content')
<div class="container mx-auto p-6">
     @php
    $user = auth()->user();
    @endphp
    <h1 class="text-2xl font-bold mb-4">Müşteriler</h1>

    {{-- Başarılı mesaj --}}
    @if(session('success'))
        <div class="bg-green-200 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

        {{-- Yeni müşteri formu --}}
        <div class="bg-white p-4 rounded shadow mb-6">
            <h2 class="text-xl font-semibold mb-2">Yeni Müşteri Ekle</h2>
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium">Ad</label>
                        <input type="text" name="first_name" class="border p-2 w-full rounded" required>
                    </div>
                    <div>
                        <label class="block font-medium">Soyad</label>
                        <input type="text" name="last_name" class="border p-2 w-full rounded" required>
                    </div>
                    <div>
                        <label class="block font-medium">Cinsiyet</label>
                        <select name="gender" class="border p-2 w-full rounded" required>

                            <option value="female">Kadın</option>
                            <option value="male">Erkek</option>
                        </select>
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
                        <label class="block font-medium">Email(isteğe Bağlı)</label>
                        <input type="email" name="email" class="border p-2 w-full rounded">
                    </div>
                    <div>
                        <label class="block font-medium">Not(isteğe Bağlı)</label>
                        <input type="text" name="note" class="border p-2 w-full rounded">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Ekle
                    </button>
                </div>
            </form>
        </div>
{{-- Arama Kutusu --}}
<div class="mb-4">
    <form action="{{ route('customers.index') }}" method="GET" class="flex gap-2">
        <input type="text" name="search" placeholder="İsim ile ara..."
               value="{{ request('search') }}"
               class="border border-gray-300 rounded px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-400">

        <button type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Ara
        </button>

        @if(request('search'))
            <a href="{{ route('customers.index') }}"
               class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
               Temizle
            </a>
        @endif
    </form>
</div>
{{-- Müşteri listesi --}}
<div class="bg-white p-4 rounded shadow">
    <h2 class="text-xl font-semibold mb-2">Müşteri Listesi</h2>
    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border p-2">Ad</th>
                <th class="border p-2">Soyad</th>
                <th class="border p-2">Cinsiyet</th>
                <th class="border p-2">Telefon</th>
                <th class="border p-2">Email</th>
                <th class="border p-2">Not</th>
                <th class="border p-2">Oluşturulma Tarihi</th>
                @if(in_array(auth()->user()->position, ['Admin','Manager']))
                <th class="border p-2">İşlemler</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @foreach($customers as $customer)
        <tr id="customer-{{ $customer->id }}">
            <td>{{ $customer->first_name }}</td>
            <td>{{ $customer->last_name }}</td>
            <td>{{ $customer->gender }}</td>
            <td>{{ $customer->phone }}</td>
            <td>{{ $customer->email }}</td>
            <td>{{ $customer->note }}</td>
            <td>{{ \Carbon\Carbon::parse($customer->created_at)->format('d/m/Y H:i') }}</td>
             @if($user->position === 'Admin' || $user->position === 'Manager')
                  <td class="flex gap-2">
                <button class="edit-btn bg-blue-500 text-white px-2 py-1 rounded"
                        data-id="{{ $customer->id }}"
                        data-first_name="{{ $customer->first_name }}"
                        data-last_name="{{ $customer->last_name }}"
                        data-gender="{{ $customer->gender }}"
                        data-phone="{{ $customer->phone }}"
                        data-email="{{ $customer->email }}"
                        data-note="{{ $customer->note }}">Düzenle</button>
                 @if($user->position === 'Admin')
                <button class="delete-btn bg-red-500 text-white px-2 py-1 rounded" data-id="{{ $customer->id }}">Sil</button>
                 @endif
            </td>
            @endif


        </tr>
        @endforeach
        </tbody>
    </table>
</div>

{{-- Düzenleme Kartı --}}
<div id="edit-card" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white p-6 rounded shadow-lg w-96">
        <h3 class="text-lg font-semibold mb-4">Müşteri Düzenle</h3>
        <form id="edit-form">
            <input type="hidden" id="edit-id">
            <div class="mb-2">
                <label>Ad</label>
                <input type="text" id="edit-first_name" class="w-full border p-2 rounded">
            </div>
            <div class="mb-2">
                <label>Soyad</label>
                <input type="text" id="edit-last_name" class="w-full border p-2 rounded">
            </div>
            <div class="mb-2">
                <label>Cinsiyet</label>
                <input type="text" id="edit-gender" class="w-full border p-2 rounded">
            </div>
            <div class="mb-2">
                <label>Telefon</label>
                <input type="text" id="edit-phone" class="w-full border p-2 rounded">
            </div>
            <div class="mb-2">
                <label>Email</label>
                <input type="text" id="edit-email" class="w-full border p-2 rounded">
            </div>
            <div class="mb-4">
                <label>Not</label>
                <input type="text" id="edit-note" class="w-full border p-2 rounded">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="cancel-btn" class="px-4 py-2 bg-gray-300 rounded">İptal</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

document.addEventListener('DOMContentLoaded', function() {
    const editCard = document.getElementById('edit-card');
    const editForm = document.getElementById('edit-form');

    // Düzenleme butonları
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-first_name').value = this.dataset.first_name;
            document.getElementById('edit-last_name').value = this.dataset.last_name;
            document.getElementById('edit-gender').value = this.dataset.gender;
            document.getElementById('edit-phone').value = this.dataset.phone;
            document.getElementById('edit-email').value = this.dataset.email;
            document.getElementById('edit-note').value = this.dataset.note;

            editCard.classList.remove('hidden');
            editCard.classList.add('flex');
        });
    });

    // İptal butonu
    document.getElementById('cancel-btn').addEventListener('click', function() {
        editCard.classList.add('hidden');
        editCard.classList.remove('flex');
    });

    // Form submit
    editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;

        const data = {
            first_name: document.getElementById('edit-first_name').value,
            last_name: document.getElementById('edit-last_name').value,
            gender: document.getElementById('edit-gender').value,
            phone: document.getElementById('edit-phone').value,
            email: document.getElementById('edit-email').value,
            note: document.getElementById('edit-note').value,
        };

        // Route uyumlu URL: PUT /customers/edit/{id}
        axios.put(`/customers/edit/${id}`, data)
            .then(res => {
                if(res.data.status === 'success') {
                    alert(res.data.message);
                    location.reload(); // Sayfayı yenileyerek güncel tabloyu göster
                }
            })
            .catch(err => console.error('Güncelleme hatası:', err));
    });

    // Silme
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            if(!confirm('Bu müşteriyi silmek istediğinize emin misiniz?')) return;

            // Route uyumlu URL: DELETE /customers/delete/{id}
            axios.delete(`/customers/delete/${id}`)
                .then(res => {
                    if(res.data.status === 'success') {
                        document.getElementById(`customer-${id}`).remove();
                        alert(res.data.message);
                    }
                })
                .catch(err => console.error('Silme hatası:', err));
        });
    });
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
