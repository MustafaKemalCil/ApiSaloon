<template>
  <div class="flex h-screen bg-gray-100">

    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md flex flex-col">
      <div class="p-4 text-xl font-bold border-b">Admin Panel</div>
      <nav class="mt-4 flex-1">
        <router-link to="/Admin/Dashboard" class="block py-2 px-4 hover:bg-gray-200">Dashboard</router-link>
        <router-link to="/Admin/Customers" class="block py-2 px-4 hover:bg-gray-200">Müşteriler</router-link>
        <router-link to="/Admin/Service" class="block py-2 px-4 hover:bg-gray-200">Hizmetler</router-link>
        <router-link to="/Admin/Appointments" class="block py-2 px-4 hover:bg-gray-200">Randevu</router-link>
        <router-link v-if="user.position === 'Admin' || user.position === 'Manager'" 
                     to="/Admin/Employees" class="block py-2 px-4 hover:bg-gray-200">Çalışan</router-link>
        <router-link to="/Admin/Profil" class="block py-2 px-4 hover:bg-gray-200">Profil</router-link>
        <button @click="logout" class="w-full text-left py-2 px-4 hover:bg-gray-200 text-red-600">Çıkış Yap</button>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">

      <!-- Top Bar -->
      <header class="bg-white shadow-md flex justify-between items-center px-6 h-16">
        <div class="text-lg font-semibold">Dashboard</div>

        <!-- Profile -->
        <div class="relative flex items-center space-x-2">
          <span class="text-gray-700">{{ user.first_name }} {{ user.last_name }}</span>

          <!-- Profile Icon -->
          <button @click="toggleProfileMenu" 
                  class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-700 font-bold hover:scale-110 transition">
            P
          </button>

          <!-- Dropdown Menu -->
          <div v-if="showProfileMenu" class="absolute right-0 top-14 w-48 bg-white shadow-lg rounded-lg border border-gray-200">
            <!-- Kullanıcı Bilgisi -->
            <div class="px-4 py-3 border-b bg-gray-50">
              <div class="text-gray-800 font-semibold">{{ user.first_name }} {{ user.last_name }}</div>
              <div class="text-gray-500 text-sm">{{ user.email }}</div>
            </div>

            <!-- Profilim -->
            <router-link to="/Admin/Profil" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
              Profilim
            </router-link>

            <!-- Çıkış Yap -->
            <button @click="logout" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">
              Çıkış Yap
            </button>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 p-6 overflow-auto">
        <router-view></router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const user = ref({
  first_name: '',
  last_name: '',
  email: '',
  position: ''
})
const showProfileMenu = ref(false)

// Örnek olarak kullanıcıyı token ile çekiyoruz
const fetchUser = async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('http://127.0.0.1:8000/api/profile', {
      headers: { Authorization: `Bearer ${token}` }
    })
    user.value = res.data
  } catch (err) {
    console.error(err)
  }
}

const toggleProfileMenu = () => {
  showProfileMenu.value = !showProfileMenu.value
}

const logout = () => {
  localStorage.removeItem('token')
  router.push('/Login')
}

// Sayfa açılır açılmaz user bilgilerini çek
onMounted(fetchUser)
</script>

<style scoped>
/* Opsiyonel: Dropdown dışında tıklayınca kapatma */
body {
  overflow-x: hidden;
}
</style>
