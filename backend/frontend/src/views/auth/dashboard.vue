<template>
  <AdminLayout>
  <div>
    <div class="mb-4 space-x-2">
      <button @click="weeklyView = true" class="px-4 py-2 bg-blue-500 text-white rounded">
        Haftalık Görünüm
      </button>
      <button @click="weeklyView = false" class="px-4 py-2 bg-green-500 text-white rounded">
        Günlük Görünüm
      </button>
    </div>

    <!-- Haftalık Görünüm -->
    <div v-if="weeklyView">
      <table class="table-fixed border-collapse border w-full text-center">
        <thead>
          <tr>
            <th class="border p-2 bg-gray-200">Saat</th>
            <th v-for="(dayName, i) in data.daysOfWeek" :key="i" class="border p-2 bg-gray-200" :colspan="data.users.length">
              {{ format(addDays(new Date(data.startOfWeek), i), 'dd-MM-yyyy') }} ({{ dayName }})
            </th>
          </tr>
          <tr>
            <th class="border p-2 bg-gray-100"></th>
            <template v-for="(dayName, i) in data.daysOfWeek" :key="'users-'+i">
              <th v-for="user in data.users" :key="dayName+'-'+user.id" class="border p-2 bg-gray-100">
                {{ user.first_name }} {{ user.last_name }}
              </th>
            </template>
          </tr>
        </thead>
        <tbody>
          <tr v-for="slot in data.timeSlots" :key="slot">
            <td class="border p-2">{{ slot }}</td>
            <template v-for="(day, i) in data.daysOfWeek" :key="'slots-'+i">
              <td v-for="user in data.users" :key="day+'-'+user.id+'-'+slot" class="border p-2">
                <div v-if="getAppointment(format(addDays(new Date(data.startOfWeek), i), 'yyyy-MM-dd'), slot, user)">
                  {{ getAppointment(format(addDays(new Date(data.startOfWeek), i), 'yyyy-MM-dd'), slot, user).customer.first_name }}
                </div>
              </td>
            </template>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Günlük Görünüm -->
    <div v-else>
      <table class="min-w-full border border-gray-300 text-sm">
        <thead>
          <tr>
            <th class="border p-2 w-16">Saat</th>
            <th v-for="user in data.users" :key="'daily-'+user.id" class="border p-2 text-center">
              {{ user.first_name }} {{ user.last_name }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="slot in data.timeSlots" :key="'daily-slot-'+slot">
            <td class="border p-1 text-center font-semibold">{{ slot }}</td>
            <td v-for="user in data.users" :key="'daily-'+user.id+'-'+slot" class="border p-1 text-xs">
              <div v-if="getAppointment(data.today, slot, user)">
                {{ getAppointment(data.today, slot, user).customer.first_name }}
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { format, addDays, parseISO } from 'date-fns'

const weeklyView = ref(true)
const data = ref({
  users: [],
  appointments: [],
  timeSlots: [],
  daysOfWeek: [],
  startOfWeek: null,
  today: format(new Date(), 'yyyy-MM-dd')
})

const fetchDashboard = async () => {
  try {
    const res = await axios.get('http://localhost:8000/api/dashboard')
    console.log('API cevabı:', res.data) // <- Bunu ekledik
    data.value = res.data
  } catch (err) {
    console.error(err)
  }
}


onMounted(fetchDashboard)

const getAppointment = (day, slot, user) => {
  return data.value.appointments.find(a => {
    const start = parseISO(a.start_datetime)
    const end = parseISO(a.end_datetime)
    const slotStart = parseISO(`${day}T${slot}`)
    const slotEnd = new Date(slotStart.getTime() + 30*60000) // 30 dakika
    return a.user_id === user.id && start < slotEnd && end > slotStart
  })
}
</script>
