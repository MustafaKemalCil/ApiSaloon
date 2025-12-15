<template>
  
  <div class="container mx-auto p-6">

    <h1 class="text-2xl font-bold mb-4">Hizmetler</h1>

    <!-- Başarı mesajı -->
    <div v-if="successMessage" class="bg-green-200 text-green-800 p-3 rounded mb-4">
      {{ successMessage }}
    </div>

    <!-- Yeni Müşteri Formu -->
    <div class="bg-white p-4 rounded shadow mb-6">
      <h2 class="text-xl font-semibold mb-2">Yeni Hizmet Ekle</h2>
      <form @submit.prevent="createservice" class="grid grid-cols-2 gap-4">

        <input v-model="newService.name" type="text" placeholder="Hizmet Adı" class="border p-2 w-full rounded" required>
        <input v-model="newService.description" type="text" placeholder="Açıklama" class="border p-2 w-full rounded" required>
        <input v-model="newService.cost" type="number" placeholder="Ücret" class="border p-2 w-full rounded">
        
        <div class="col-span-2 mt-4">
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Ekle</button>
        </div>
      </form>
    </div>

    <!-- Arama -->
    <div class="mb-4 flex gap-2">
      <input v-model="searchQuery" type="text" placeholder="İsim ile ara..."
             class="border border-gray-300 rounded px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-400">
      <button @click="searchservice" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Ara</button>
      <button v-if="searchQuery" @click="clearSearch" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Temizle</button>
    </div>

    <!-- Müşteri Listesi -->
    <div class="bg-white p-4 rounded shadow">
      <h2 class="text-xl font-semibold mb-2">Hizmet Listesi</h2>
      <table class="w-full border-collapse border border-gray-300">
        <thead>
          <tr class="bg-gray-100">
            <th class="border p-2">name</th>
            <th class="border p-2">description</th>
            <th class="border p-2">cost</th>
           
          </tr>
        </thead>
        <tbody>
          <tr v-for="service in filteredservices" :key="service.id">
            <td>{{ service.name }}</td>
            <td>{{ service.description }}</td>
            <td>{{ service.cost }}</td>
             
            <td class="flex gap-2">
              <button @click="openEditModal(service)" class="bg-blue-500 text-white px-2 py-1 rounded">Düzenle</button>
              <button @click="deleteservice(service.id)" class="bg-red-500 text-white px-2 py-1 rounded">Sil</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Düzenleme Modal -->
    <div v-if="showEditModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded shadow-lg w-96">
        <h3 class="text-lg font-semibold mb-4">Müşteri Düzenle</h3>
        <form @submit.prevent="updateservice">
          <input type="hidden" v-model="editService.id">
          <input v-model="editService.name" placeholder="Ad" class="w-full border p-2 rounded mb-2">
          <input v-model="editService.description" placeholder="Açıklama" class="w-full border p-2 rounded mb-2">
          <input v-model="editService.cost" placeholder="Ücret" class="w-full border p-2 rounded mb-2">
          
          <div class="flex justify-end gap-2">
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

 

const services = ref([])
const newService = ref({ name: '', description: '', cost: null })
const editService = ref({ id: null, name: '', description: '', cost: null })
const showEditModal = ref(false)
const successMessage = ref('')
const searchQuery = ref('')

// API ile Hizmet getir
const fetchservices = async () => {
  const res = await axios.get('http://localhost:8000/api/service')
  services.value = res.data
}
onMounted(fetchservices)

// Yeni Mizmet ekle
const createservice = async () => {
  try {
    await axios.post('http://localhost:8000/api/service', newService.value)
    successMessage.value = 'Hizmet başarıyla eklendi!'
    Object.assign(newService.value, {name:'', description:'',   cost:'' })
    fetchservices()
  } catch (err) {
    console.error(err)
    alert('Hata: ' + err.response.data.message)
  }
}

// Düzenleme modalını aç
const openEditModal = (service) => {
  editService.value = {...service}
  showEditModal.value = true
}

// Müşteri güncelle
const updateservice = async () => {
  try {
    await axios.put(`http://localhost:8000/api/service/${editService.value.id}`, editService.value)
    successMessage.value = 'Hizmet başarıyla güncellendi!'
    showEditModal.value = false
    fetchservices()
  } catch (err) {
    console.error(err)
    alert('Hata: ' + err.response.data.message)
  }
}

// Müşteri sil
const deleteservice = async (id) => {
  if(!confirm('Bu müşteriyi silmek istediğinize emin misiniz?')) return
  try {
    await axios.delete(`http://localhost:8000/api/service/${id}`)
    successMessage.value = 'Hizmet başarıyla silindi!'
    fetchservices()
  } catch (err) {
    console.error(err)
    alert('Silme hatası!')
  }
}

// Arama ve filtreleme
const filteredservices = computed(() => {
  if(!searchQuery.value) return services.value
  return services.value.filter(c => c.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
})
const searchservice = () => {}
const clearSearch = () => searchQuery.value = ''

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString() + ' ' + date.toLocaleTimeString()
}
</script>
