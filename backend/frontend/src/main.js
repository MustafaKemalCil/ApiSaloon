import { createApp } from 'vue'
import './assets/main.css'
import App from './App.vue'
import router from './router'

import axios from 'axios'

// 🔥 API instance
const api = axios.create({
    baseURL: "http://localhost:8000/api",
    headers: {
        "Content-Type": "application/json"
    }
})

// 🔥 Request Interceptor → Her isteğe token ekler
api.interceptors.request.use(
    config => {
        const token = localStorage.getItem("token")
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    error => Promise.reject(error)
)

// 🔥 Response Interceptor → 401 → token sil + Login’e yönlendir
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem("token")
            router.push('/Login')
        }
        return Promise.reject(error)
    }
)

const app = createApp(App)

// ✔ Vue global: this.$api
app.config.globalProperties.$api = api

app.use(router)
app.mount('#app')
