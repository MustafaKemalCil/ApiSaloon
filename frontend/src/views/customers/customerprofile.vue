<template>
  <div class="container mx-auto p-6">

    <!-- Başlık -->
    <h1 class="text-2xl font-bold mb-4">Müşteri Profili</h1>
    <!-- ÖDEME MODALI -->
<div v-if="showPaymentModal" class="fixed inset-0 bg-black/50 flex items-center justify-center">
  <div class="bg-white p-6 rounded shadow w-96">

    <h2 class="text-xl font-semibold mb-4">
      Ödeme Ekle – {{ selectedAppointment?.service }}
    </h2>
    <!-- ÖDEME GEÇMİŞİ -->
    <div class="mb-4">
      <h3 class="font-semibold mb-2">Ödeme Geçmişi</h3>

      <div v-if="paymentHistory.length === 0" class="text-gray-500 text-sm">
        Bu randevu için ödeme yapılmamış.
      </div>

      <ul v-else class="space-y-2 max-h-40 overflow-y-auto">
        <li v-for="p in paymentHistory" :key="p.id" class="border p-2 rounded">
          <div class="text-sm">
            <strong>{{ p.amount }}₺</strong>  
            <span class="text-gray-500"> - {{ p.created_at }}</span>
          </div>
          <div class="text-xs text-gray-600">{{ p.note }}</div>
        </li>
      </ul>
    </div>


    <div class="mb-3">
      <label class="block font-semibold mb-1">Tutar (₺)</label>
      <input v-model="paymentAmount" type="number" min="1"
             class="border p-2 rounded w-full" />
    </div>

    <div class="mb-3">
      <label class="block font-semibold mb-1">Not (opsiyonel)</label>
      <textarea v-model="paymentNote" class="border p-2 rounded w-full"></textarea>
    </div>

    <div class="flex justify-end gap-2 mt-4">
      <button @click="showPaymentModal=false" class="px-4 py-2 bg-gray-300 rounded">
        İptal
      </button>

      <button @click="savePayment"
              class="px-4 py-2 bg-green-600 text-white rounded">
        Kaydet
      </button>
    </div>

  </div>
</div>

    <!-- Müşteri Bilgileri -->
    <div class="bg-white p-4 rounded shadow mb-6 flex justify-between items-center">
      <div>
        <p class="text-lg font-semibold">{{ customer.first_name }} {{ customer.last_name }}</p>
        <p>Cinsiyet: {{ customer.gender }}</p>
        <p>Telefon: {{ customer.phone || '-' }}</p>
        <p>Email: {{ customer.email || '-' }}</p>
        <p>Not: {{ customer.note || '-' }}</p>
      </div>
      <div class="flex gap-2">
        <button @click="openEditModal(customer)" class="bg-blue-500 text-white px-4 py-2 rounded">Düzenle</button>
       
        
        <button @click="createAppointment(customer.id)" class="bg-green-500 text-white px-4 py-2 rounded">Yeni Randevu</button>
      </div>
    </div>

    <!-- İstatistikler -->
    <div class="bg-white p-4 rounded shadow mb-6 grid grid-cols-3 gap-4 text-center">
       
      <div class="p-2 border rounded">
        <p class="font-semibold text-lg">{{ totalSpent }}₺</p>
        <p>Toplam Hizmet</p>
      </div>
      <div class="p-2 border rounded">
        <p class="font-semibold text-lg">{{ totalPaid  }}₺</p>
        <p>Toplam Ödeme</p>
      </div>
      <div class="p-2 border rounded">
        <p class="font-semibold text-lg">{{ totalSpent-totalPaid  }}₺</p>
        <p>Toplam Borç</p>
      </div>
      <div class="p-2 border rounded">
        <p class="font-semibold text-lg">{{ appointments.length }}</p>
        <p>Toplam Randevu</p>
      </div>
      <div class="p-2 border rounded">
        <p class="font-semibold text-lg">{{ lastAppointmentDate || '-' }}</p>
        <p>Son Randevu</p>
      </div>
    </div>

    <!-- Randevu Geçmişi -->
    <div class="bg-white p-4 rounded shadow">
      <h2 class="text-xl font-semibold mb-2">Randevu Geçmişi</h2>

      <!-- Filtre / Arama -->
      <div class="mb-4 flex gap-2">
        <input v-model="filterDate" type="date" class="border p-2 rounded">
        <input v-model="filterService" type="text" placeholder="Hizmet ara..." class="border p-2 rounded">
        <button @click="applyFilter" class="bg-blue-500 text-white px-4 py-2 rounded">Filtrele</button>
        <button @click="clearFilter" class="bg-gray-400 text-white px-4 py-2 rounded">Temizle</button>
      </div>

      <table class="w-full border-collapse border border-gray-300">
  <thead>
    <tr class="bg-gray-100">
      <th class="border p-2">Tarih</th>
      <th class="border p-2">Saat</th>
      <th class="border p-2">Hizmet</th>
      <th class="border p-2">Ücret</th>
      <th class="border p-2">Ödenen</th> <!-- yeni sütun -->
      <th class="border p-2">Çalışan</th>
      <th class="border p-2">Not</th>
      <th class="border p-2">Ödemeler</th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="appt in filteredAppointments" :key="appt.id">
      <td>{{ formatDate(appt.start_datetime) }}</td>
      <td>{{ formatTime(appt.start_datetime) }}</td>
      <td>{{ appt.service }}</td>
      <td>{{ appt.cost }}₺</td>
      <td>{{ appt.total_payment }}₺</td> <!-- ödenen miktar -->
      <td>{{ appt.worker_name }}</td>
      <td>{{ appt.note || '-' }}</td>
      <td>
        <button 
          @click="openPaymentModal(appt)" 
          class="bg-green-500 text-white px-3 py-1 rounded">
          Ödeme Ekle
        </button>
      </td>
    </tr>
    <tr v-if="filteredAppointments.length === 0">
      <td colspan="8" class="text-center p-2">Randevu bulunamadı.</td>
    </tr>
  </tbody>
</table>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
const paymentHistory = ref([])

const route = useRoute()
const router = useRouter()

const customerId = route.params.id
const customer = ref({})
const appointments = ref([])
console.log("Gelen ID:", route.params.id)
// Filtreler

const filterDate = ref('')
const filterService = ref('')
 
// API çağrısı
const fetchCustomer = async () => {
  const res = await axios.get(`http://localhost:8000/api/customers/${customerId}`)
  customer.value = res.data.customer
  appointments.value = res.data.appointments
}
console.log("📌 Müşteri :", customer)
onMounted(fetchCustomer)
// Ödeme modalı state
const showPaymentModal = ref(false)
const selectedAppointment = ref(null)

const paymentAmount = ref('')
const paymentNote = ref('')

// Modal açma
const openPaymentModal = async (appt) => {
  selectedAppointment.value = appt
  paymentAmount.value = ''
  paymentNote.value = ''
  showPaymentModal.value = true

  try {
    const res = await axios.get(`http://localhost:8000/api/appointments/${appt.id}/payments`)
    paymentHistory.value = res.data.payments
    console.log('Ödeme geçmişi:', paymentHistory.value)
  } catch (err) {
    console.error(err)
    paymentHistory.value = []
  }
}

// Ödeme kaydetme
const savePayment = async () => {
  if (!paymentAmount.value) {
    alert("Tutar gir!");
    return;
  }

  try {
    await axios.post(
      `http://localhost:8000/api/appointments/${selectedAppointment.value.id}/payments`,
      {
        amount: paymentAmount.value,
        note: paymentNote.value,
        customer_id: customer.value.id
      }
    );

    // Ödeme eklenince geçmişi güncelle
    const res = await axios.get(`http://localhost:8000/api/appointments/${selectedAppointment.value.id}/payments`)
    paymentHistory.value = res.data.payments

    alert("Ödeme kaydedildi!");
    paymentAmount.value = ''
    paymentNote.value = ''
  } catch (err) {
    alert(err.response?.data?.error || "Hata oluştu");
  }
};
// Randevu filtreleme
const filteredAppointments = computed(() => {
  return appointments.value.filter(appt => {
    const matchDate = filterDate.value ? appt.start_datetime.startsWith(filterDate.value) : true
    const matchService = filterService.value ? appt.service.toLowerCase().includes(filterService.value.toLowerCase()) : true
    return matchDate && matchService
  })
})

const totalSpent = computed(() => appointments.value.reduce((sum, a) => sum +  parseFloat(a.cost || 0), 0))
const lastAppointmentDate = computed(() => {
  if(!appointments.value.length) return ''
  return appointments.value.reduce((latest, a) => a.start_datetime > latest ? a.start_datetime : latest, appointments.value[0].start_datetime)
})
// appointments.value her randevunun içinde total_payment var
const totalPaid = computed(() => {
  return appointments.value.reduce((sum, appt) => sum + (parseFloat(appt.total_payment) || 0), 0);
});
// Fonksiyonlar
const applyFilter = () => {}
const clearFilter = () => { filterDate.value=''; filterService.value='' }
const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString()
const formatTime = (dateStr) => new Date(dateStr).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})

// Placeholder fonksiyonlar
const openEditModal = (customer) => router.push({ name:'EditCustomer', params:{id:customer.id} })
 
const createAppointment = (id) => router.push({ name:'CreateAppointment', params:{customerId:id} })
</script>

<style scoped>
/* Küçük stil iyileştirmeleri */
</style>
