// src/router.js
import { createRouter, createWebHistory } from 'vue-router'
import AdminLayout from '@/AdminLayout.vue'
import MainLayout from '@/MainLayout.vue'
import Home from '@/views/pages/Home.vue'
import About from '@/views/pages/About.vue'
import Contact from '@/views/pages/Contact.vue'  // <<< bunu ekle!
import Login from '@/views/auth/login.vue'
import Dashboard from '@/views/auth/dashboard.vue'
import Appointments from '@/views/appointments/index.vue'
import CustomerProfile from '@/views/customers/customerprofile.vue'
import Customer from '@/views/customers/index.vue'
import Employees from '@/views/employees/index.vue'
import Profil from '@/views/profile/index.vue'
import Service from '@/views/service/index.vue'







const routes = [
 
  {
   path: '/',
    component: MainLayout,
    children: [
       { path: '', name: 'Home', component: Home },
  { path: 'About', name: 'About', component: About },
  { path: 'Contact', name: 'Contact', component: Contact },  
  { path: '/Login', name: 'Login', component: Login }, 
    ]
  },

 
    // Admin panel
  {
  path: '/Admin',
  component: AdminLayout,
  meta: { requiresAuth: true },
  children: [
    { path: 'Dashboard', name: 'Dashboard', component: Dashboard },
    { path: 'Appointments', name: 'Appointments', component: Appointments },
    { path: 'Customers', name: 'Customers', component: Customer },
    { path: 'customers/:id', name: 'CustomerProfile', component: CustomerProfile },
    { path: 'Employees', name: 'Employees', component: Employees },
    { path: 'Profil', name: 'Profil', component: Profil },
    { path: 'Service', name: 'Service', component: Service },
  ],
}

]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// ---------------------------------------------------
// 🔥 TAM OLARAK BURAYA KOYACAKSIN
// ---------------------------------------------------
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem("token")

  if (to.matched.some(record => record.meta.requiresAuth) && !token) {
    return next('/Login')
  }

  next()
})


export default router
