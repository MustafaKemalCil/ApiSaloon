<template>
   
  <div v-if="user" class="container mx-auto max-w-2xl mt-10 p-6 bg-white shadow rounded">

    <h1 class="text-2xl font-bold mb-4">👤 Profil Bilgileri</h1>

    <div v-if="successMsg" class="bg-green-200 text-green-800 p-3 rounded mb-4">
      {{ successMsg }}
    </div>
    <div v-if="errorMsg" class="bg-red-200 text-red-800 p-3 rounded mb-4">
      {{ errorMsg }}
    </div>

    <!-- Profil Bilgileri Formu -->
    <form @submit.prevent="updateProfile">
      <div class="mb-4">
        <label class="font-semibold">Ad</label>
        <input v-model="user.first_name" type="text"
               class="w-full p-2 border rounded mt-1">
      </div>

      <div class="mb-4">
        <label class="font-semibold">Soyad</label>
        <input v-model="user.last_name" type="text"
               class="w-full p-2 border rounded mt-1">
      </div>

      <div class="mb-4">
        <label class="font-semibold">Email</label>
        <input v-model="user.email" type="email"
               class="w-full p-2 border rounded mt-1">
      </div>

      <div class="mb-4">
        <label class="font-semibold">Telefon</label>
        <input v-model="user.phone" type="text"
               class="w-full p-2 border rounded mt-1">
      </div>

      <div class="text-right">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
          Güncelle
        </button>
      </div>
    </form>

    <hr class="my-6">

    <!-- Şifre Değiştirme Formu -->
    <h2 class="text-xl font-bold mb-4">🔐 Şifre Değiştir</h2>

    <form @submit.prevent="updatePassword">
      <div class="mb-4">
        <label class="font-semibold">Mevcut Şifre</label>
        <input v-model="passwordForm.current_password" type="password"
               class="w-full p-2 border rounded mt-1" required>
      </div>

      <div class="mb-4">
        <label class="font-semibold">Yeni Şifre</label>
        <input v-model="passwordForm.new_password" type="password"
               class="w-full p-2 border rounded mt-1" required>
      </div>

      <div class="mb-4">
        <label class="font-semibold">Yeni Şifre (Tekrar)</label>
        <input v-model="passwordForm.new_password_confirmation" type="password"
               class="w-full p-2 border rounded mt-1" required>
      </div>

      <div class="text-right">
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">
          Şifreyi Güncelle
        </button>
      </div>
    </form>

  </div>

  <div v-else class="text-center p-10 text-gray-500">
    Yükleniyor...
  </div>
 
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const user = ref(null)
const errorMsg = ref('')
const successMsg = ref('')

const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

// Profil verilerini çek
const fetchUser = async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('http://127.0.0.1:8000/api/profile', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
    user.value = res.data
  } catch (err) {
    errorMsg.value = 'Kullanıcı bilgileri alınamadı!'
    console.error(err)
  }
}

// Profil güncelleme fonksiyonu
const updateProfile = async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.post('http://127.0.0.1:8000/api/profile/update', {
      first_name: user.value.first_name,
      last_name: user.value.last_name,
      email: user.value.email,
      phone: user.value.phone
    }, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
    successMsg.value = res.data.message
    errorMsg.value = ''
    user.value = res.data.user
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Profil güncellenemedi!'
    successMsg.value = ''
    console.error(err)
  }
}

// Şifre güncelleme fonksiyonu
const updatePassword = async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.post('http://127.0.0.1:8000/api/profile/password-update', {
      current_password: passwordForm.value.current_password,
      new_password: passwordForm.value.new_password,
      new_password_confirmation: passwordForm.value.new_password_confirmation
    }, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
    successMsg.value = res.data.message
    errorMsg.value = ''
    // Formu temizle
    passwordForm.value.current_password = ''
    passwordForm.value.new_password = ''
    passwordForm.value.new_password_confirmation = ''
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Şifre güncellenemedi!'
    successMsg.value = ''
    console.error(err)
  }
}

onMounted(fetchUser)
</script>
