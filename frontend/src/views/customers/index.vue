<template>
  
  <div class="container mx-auto p-6">

    <h1 class="text-2xl font-bold mb-4">Müşteriler</h1>

    <!-- Başarı mesajı -->
    <div v-if="successMessage" class="bg-green-200 text-green-800 p-3 rounded mb-4">
      {{ successMessage }}
    </div>

    <!-- Yeni Müşteri Formu -->
    <div class="bg-white p-4 rounded shadow mb-6">
      <h2 class="text-xl font-semibold mb-2">Yeni Müşteri Ekle</h2>
      <form @submit.prevent="createCustomer" class="grid grid-cols-2 gap-4">

        <input v-model="newCustomer.first_name" type="text" placeholder="Ad" class="border p-2 w-full rounded" required>
        <input v-model="newCustomer.last_name" type="text" placeholder="Soyad" class="border p-2 w-full rounded" required>

        <select v-model="newCustomer.gender" class="border p-2 w-full rounded" required>
          <option value="female">Kadın</option>
          <option value="male">Erkek</option>
        </select>

        <input v-model="newCustomer.phone" type="tel" placeholder="+90 555 555 55 55" class="border p-2 w-full rounded">
        <input v-model="newCustomer.email" type="email" placeholder="Email" class="border p-2 w-full rounded">
        <input v-model="newCustomer.note" type="text" placeholder="Not" class="border p-2 w-full rounded">

        <div class="col-span-2 mt-4">
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Ekle</button>
        </div>
      </form>
    </div>

    <!-- Arama -->
    <div class="mb-4 flex gap-2">
      <input v-model="searchQuery" type="text" placeholder="İsim ile ara..."
             class="border border-gray-300 rounded px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-400">
      <button @click="searchCustomer" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Ara</button>
      <button v-if="searchQuery" @click="clearSearch" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Temizle</button>
    </div>

    <!-- Müşteri Listesi -->
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
            <th class="border p-2">İşlemler</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="customer in filteredCustomers" :key="customer.id">
            <td>{{ customer.first_name }}</td>
            <td>{{ customer.last_name }}</td>
            <td>{{ customer.gender }}</td>
            <td>{{ customer.phone || '-' }}</td>
            <td>{{ customer.email || '-' }}</td>
            <td>{{ customer.note || '-' }}</td>
            <td>{{ formatDate(customer.created_at) }}</td>
            <td class="flex gap-2">
               <!-- Profil Butonu -->
  <button 
  type="button"
  @click="$router.push({ name: 'CustomerProfile', params: { id: customer.id } })"
  class="px-4 py-2 bg-green-500 text-white rounded"
> 
  Profil
</button>

              <button @click="openEditModal(customer)" class="bg-blue-500 text-white px-2 py-1 rounded">Düzenle</button>
              <button @click="deleteCustomer(customer.id)" class="bg-red-500 text-white px-2 py-1 rounded">Sil</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Düzenleme Modal -->
    <div v-if="showEditModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded shadow-lg w-96">
        <h3 class="text-lg font-semibold mb-4">Müşteri Düzenle</h3>
        <form @submit.prevent="updateCustomer">
          <input type="hidden" v-model="editCustomer.id">

  <label class="block text-sm font-medium mb-1">Ad</label>
  <input v-model="editCustomer.first_name" class="w-full border p-2 rounded mb-3">

  <label class="block text-sm font-medium mb-1">Soyad</label>
  <input v-model="editCustomer.last_name" class="w-full border p-2 rounded mb-3">

  <label class="block text-sm font-medium mb-1">Cinsiyet</label>
  <input v-model="editCustomer.gender" class="w-full border p-2 rounded mb-3">

  <label class="block text-sm font-medium mb-1">Telefon</label>
  <input v-model="editCustomer.phone" class="w-full border p-2 rounded mb-3">

  <label class="block text-sm font-medium mb-1">Email</label>
  <input v-model="editCustomer.email" class="w-full border p-2 rounded mb-3">

  <label class="block text-sm font-medium mb-1">Not</label>
  <input v-model="editCustomer.note" class="w-full border p-2 rounded mb-4">

          <div class="flex justify-end gap-2">
             <!-- Profil Butonu -->
  <button 
    type="button"
    @click="$router.push({ name: 'CustomerProfile', params: { id: editCustomer.id } })"
    class="px-4 py-2 bg-green-500 text-white rounded"
  >
    Profil
  </button>
            <button type="button" @click="showEditModal=false" class="px-4 py-2 bg-gray-300 rounded">İptal</button>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Kaydet</button>
          </div>
        </form>
      </div>
    </div>

  </div>
  
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

 

const customers = ref([])
const newCustomer = ref({first_name:'', last_name:'', gender:'female', phone:'', email:'', note:''})
const editCustomer = ref({})
const showEditModal = ref(false)
const successMessage = ref('')
const searchQuery = ref('')

// API ile müşterileri getir
const fetchCustomers = async () => {
  const res = await axios.get('http://localhost:8000/api/customers')
  customers.value = res.data
}
onMounted(fetchCustomers)

// Yeni müşteri ekle
const createCustomer = async () => {
  try {
    await axios.post('http://localhost:8000/api/customers', newCustomer.value)
    successMessage.value = 'Müşteri başarıyla eklendi!'
    Object.assign(newCustomer.value, {first_name:'', last_name:'', gender:'female', phone:'', email:'', note:''})
    fetchCustomers()
  } catch (err) {
    console.error(err)
    alert('Hata: ' + err.response.data.message)
  }
}

// Düzenleme modalını aç
const openEditModal = (customer) => {
  editCustomer.value = {...customer}
  showEditModal.value = true
}

// Müşteri güncelle
const updateCustomer = async () => {
  try {
    await axios.put(`http://localhost:8000/api/customers/${editCustomer.value.id}`, editCustomer.value)
    successMessage.value = 'Müşteri başarıyla güncellendi!'
    showEditModal.value = false
    fetchCustomers()
  } catch (err) {
    console.error(err)
    alert('Hata: ' + err.response.data.message)
  }
}

// Müşteri sil
const deleteCustomer = async (id) => {
  if(!confirm('Bu müşteriyi silmek istediğinize emin misiniz?')) return
  try {
    await axios.delete(`http://localhost:8000/api/customers/${id}`)
    successMessage.value = 'Müşteri başarıyla silindi!'
    fetchCustomers()
  } catch (err) {
    console.error(err)
    alert('Silme hatası!')
  }
}

// Arama ve filtreleme
const filteredCustomers = computed(() => {
  if(!searchQuery.value) return customers.value
  return customers.value.filter(c => c.first_name.toLowerCase().includes(searchQuery.value.toLowerCase()))
})
const searchCustomer = () => {}
const clearSearch = () => searchQuery.value = ''

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString() + ' ' + date.toLocaleTimeString()
}
</script>
