<template>
 
  <div class="container mx-auto p-6">

    <!-- Başlık -->
    <h1 class="text-2xl font-bold mb-4">Çalışanlar</h1>

    <!-- Başarı mesajı -->
    <div v-if="successMessage" class="bg-green-200 text-green-800 p-3 rounded mb-4">
      {{ successMessage }}
    </div>

    <!-- Yeni Çalışan Ekle Butonu -->
    <button @click="showForm = !showForm"
      class="bg-blue-600 text-white px-4 py-2 rounded mb-4">
      Yeni Çalışan Ekle
    </button>

    <!-- Yeni Çalışan Formu -->
    <div v-if="showForm" class="bg-white p-4 rounded shadow mb-6">
      <h2 class="text-xl font-semibold mb-2">Yeni Çalışan Ekle</h2>
      <form @submit.prevent="createEmployee" class="space-y-3">

        <input v-model="newEmployee.first_name" type="text" placeholder="Ad" class="border p-2 w-full rounded" required>
        <input v-model="newEmployee.last_name" type="text" placeholder="Soyad" class="border p-2 w-full rounded" required>
        <input v-model="newEmployee.email" type="email" placeholder="Email" class="border p-2 w-full rounded" required>
        <input v-model="newEmployee.phone" type="tel" placeholder="+90 555 555 55 55" class="border p-2 w-full rounded">
        <input v-model="newEmployee.password" type="password" placeholder="Şifre" class="border p-2 w-full rounded" required>
        <input v-model="newEmployee.password_confirmation" type="password" placeholder="Şifre Doğrulama" class="border p-2 w-full rounded" required>

        <select v-model="newEmployee.position" class="border p-2 w-full rounded">
          <option value="Employee">Çalışan</option>
          <option value="Manager">Müdür</option>
        </select>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded mt-2">Kaydet</button>
      </form>
    </div>

    <!-- Düzenle Modal -->
    <div v-if="showEditModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded shadow-lg w-full max-w-md relative">
        <h2 class="text-xl font-semibold mb-4">Çalışan Düzenle</h2>
        <form @submit.prevent="updateEmployee">

          <input type="text" v-model="editEmployee.first_name" placeholder="Ad" class="border p-2 w-full rounded mb-2" required>
          <input type="text" v-model="editEmployee.last_name" placeholder="Soyad" class="border p-2 w-full rounded mb-2" required>
          <input type="email" v-model="editEmployee.email" placeholder="Email" class="border p-2 w-full rounded mb-2" required>
          <input type="tel" v-model="editEmployee.phone" placeholder="+90 555 555 55 55" class="border p-2 w-full rounded mb-2">
          <select v-model="editEmployee.position" class="border p-2 w-full rounded mb-2">
            <option value="Employee">Çalışan</option>
            <option value="Manager">Müdür</option>
          </select>

          <div class="flex justify-end space-x-2">
            <button type="button" @click="showEditModal = false" class="bg-gray-400 text-white px-4 py-2 rounded">İptal</button>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Güncelle</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Çalışan Listesi -->
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
          <tr v-for="employee in employees" :key="employee.id">
            <td class="border p-2">{{ employee.first_name }}</td>
            <td class="border p-2">{{ employee.last_name }}</td>
            <td class="border p-2">{{ employee.email }}</td>
            <td class="border p-2">{{ employee.phone || '-' }}</td>
            <td class="border p-2">{{ employee.position || '-' }}</td>
            <td class="border p-2 space-x-2">
              <button @click="openEditModal(employee)" class="bg-yellow-400 px-2 py-1 rounded text-white">Düzenle</button>
              <button @click="deleteEmployee(employee.id)" class="bg-red-500 px-2 py-1 rounded text-white">Sil</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
  
  

</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// Backend URL'si
const API_URL = 'http://localhost:8000/api/employees'

const employees = ref([])
const newEmployee = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  position: 'Employee'
})

const editEmployee = ref({})
const showForm = ref(false)
const showEditModal = ref(false)
const successMessage = ref('')

// API ile çalışanları getir
const fetchEmployees = async () => {
  try {
    const res = await axios.get(API_URL)
    employees.value = res.data
  } catch (err) {
    alert('Hata: ' + (err.response?.data?.message || err.message))
  }
}

onMounted(fetchEmployees)

// Yeni çalışan ekleme
const createEmployee = async () => {
  try {
    await axios.post(API_URL, newEmployee.value)
    successMessage.value = 'Çalışan başarıyla eklendi!'
    showForm.value = false
    Object.assign(newEmployee.value, {first_name:'', last_name:'', email:'', phone:'', password:'', password_confirmation:'', position:'Employee'})
    fetchEmployees()
  } catch (err) {
    alert('Hata: ' + (err.response?.data?.message || err.message))
  }
}

// Düzenleme modalını aç
const openEditModal = (employee) => {
  editEmployee.value = {...employee}
  showEditModal.value = true
}

// Çalışanı güncelle
const updateEmployee = async () => {
  try {
    await axios.put(`${API_URL}/${editEmployee.value.id}`, editEmployee.value)
    successMessage.value = 'Çalışan başarıyla güncellendi!'
    showEditModal.value = false
    fetchEmployees()
  } catch (err) {
    alert('Hata: ' + (err.response?.data?.message || err.message))
  }
}

// Çalışanı sil
const deleteEmployee = async (id) => {
  if (!confirm('Silmek istediğinize emin misiniz?')) return
  try {
    await axios.delete(`${API_URL}/${id}`)
    successMessage.value = 'Çalışan başarıyla silindi!'
    fetchEmployees()
  } catch (err) {
    alert('Hata: ' + (err.response?.data?.message || err.message))
  }
}
</script>
