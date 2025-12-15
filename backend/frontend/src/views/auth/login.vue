<template>
  <div class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg rounded-xl p-10 w-full max-w-md text-center">
      <h2 class="text-2xl font-semibold mb-6 text-gray-800">Giriş Yap</h2>

      <div v-if="error" class="mb-3 text-sm text-red-600">
        {{ error }}
      </div>

      <form @submit.prevent="login" class="space-y-5">
        <input 
          v-model="email"
          type="email"
          placeholder="E-posta"
          class="mx-auto block w-80 px-4 py-3 border text-base rounded-lg focus:ring focus:ring-blue-200 focus:outline-none"
          required
        >

        <input 
          v-model="password"
          type="password"
          placeholder="Şifre"
          class="mx-auto block w-80 px-4 py-3 border text-base rounded-lg focus:ring focus:ring-blue-200 focus:outline-none"
          required
        >

        <button type="submit"
          class="mx-auto block w-80 bg-blue-600 text-white py-3 text-base rounded-lg hover:bg-blue-700 transition font-medium">
          Giriş Yap
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue"
import axios from "axios"
import { useRouter } from 'vue-router'

const email = ref("")
const password = ref("")
const error = ref("")
const router = useRouter()

const login = async () => {
  try {
    const res = await axios.post("http://127.0.0.1:8000/api/login", {
      email: email.value,
      password: password.value
    })

    // Token ve user localStorage'a kaydet
    localStorage.setItem("token", res.data.token)
    localStorage.setItem("user", JSON.stringify(res.data.user))

    error.value = ""

    // Profil sayfasına git
    router.push({ name: 'Profil' })

  } catch (err) {
    if(err.response && err.response.status === 401){
      error.value = "E-posta veya şifre yanlış!"
    } else {
      error.value = "Sunucu hatası!"
      console.error(err)
    }
  }
}
</script>

