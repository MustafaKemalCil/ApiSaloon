  <template>
     
    <div class="container mx-auto p-6">

      <!-- Başarı mesajı -->
      <div v-if="successMessage" class="bg-green-200 text-green-800 p-3 rounded mb-4">
        {{ successMessage }}
      </div>

      <!-- Randevu Formu -->
      <div class="flex justify-start w-max bg-white p-4 rounded shadow">
        <form @submit.prevent="submitAppointment" class="space-y-4 w-full max-w-md">
          <!-- Hizmet Seçimi (Searchable) -->
          <div>
            <label class="block font-semibold">Hizmet</label>
            
            <input 
              type="text" 
              v-model="serviceSearch" 
              placeholder="Hizmet ara..." 
              class="border rounded p-2 w-full"
              @focus="isServiceListOpen = true"
            >
            
            <ul v-if="isServiceListOpen && filteredServices.length" class="border rounded mt-1 max-h-40 overflow-y-auto bg-white">
              <li 
                v-for="service in filteredServices.slice(0,5)" 
                :key="service.id" 
                @click="selectService(service)" 
                class="p-2 hover:bg-gray-100 cursor-pointer"
              >
                {{ service.name }} ({{ service.cost ?? 0 }}₺)
              </li>
            </ul>
          </div>

          <!-- Ücret (otomatik dolacak ama değiştirilebilir) -->
          <div>
            <label class="block font-semibold">Ücret</label>
            <input 
              type="number" 
              v-model="form.cost" 
              class="border rounded p-2 w-full"
            >
          </div>
          <!-- Müşteri -->
          <div>
            <label class="block font-semibold">Müşteri</label>
            <select v-model="form.customer_id" class="border rounded p-2 w-full" required>
              <option value="">Seçiniz</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                {{ customer.first_name }} {{ customer.last_name }}
              </option>
            </select>
          </div>
       
          <!-- Çalışan -->
          <div>
            <label class="block font-semibold">Çalışan</label>
            <select v-model="form.employee_id" class="border rounded p-2 w-full" required>
              <option value="">Seçiniz</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.first_name }} {{ user.last_name }}
              </option>
            </select>
          </div>
          
          <!-- Tarih -->
          <div>
            <label class="block font-semibold">Tarih</label>
            <input type="date" v-model="form.date" class="border rounded p-2 w-full" :min="today" required>
          </div>

          <!-- Saat Seçimi -->
          <div>
            <label class="block font-semibold">Saat Seçimi</label>
            <div class="grid grid-cols-4 gap-2 mt-2">
              <button 
                v-for="(slot, index) in timeSlots" 
                :key="index"
                type="button"
                :class="slotButtonClass(slot)"
                :disabled="slot.isBusy"
                @click="toggleSlot(index)"
              >
                {{ slot.time }}
              </button>
            </div>
          </div>

          <!-- Gizli alanlar -->
          <input type="hidden" v-model="form.start_datetime">
          <input type="hidden" v-model="form.end_datetime">
          
          <!-- Başlık / Not -->
          <div>
            <label class="block font-semibold">Not</label>
            <textarea v-model="form.note" placeholder="Açıklama..." class="border rounded p-2 w-full mt-2"></textarea>
          </div>

          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Kaydet</button>

        </form>
      </div>
 
    </div>
  
  </template>

  <script setup>
  import { ref, onMounted, computed, watch } from 'vue'
  import axios from 'axios'
  import dayjs from 'dayjs' 
  const token = localStorage.getItem('token')
  if(token){
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + token
  }
const isServiceListOpen = ref(false)

  const customers = ref([])
  const users = ref([])
  const appointments = ref([])
  const today = ref(dayjs().format('YYYY-MM-DD'))
  const successMessage = ref('')

  const showView = ref('weekly')
  const form = ref({
    customer_id: '',
    employee_id: '',
    date: today.value,
    start_datetime: '',
    end_datetime: '',
    service: '',
    note: '',
    cost:0
  })

  const timeSlots = ref([])
  const selectedSlots = ref([])
 // ❌ Buraya ekle: Hizmetler ve search
const services = ref([])           // API'den çekilecek hizmetler
const serviceSearch = ref('')      // Arama input
const filteredServices = computed(() => {
  if (!serviceSearch.value) return services.value
  return services.value.filter(s =>
    s.name.toLowerCase().includes(serviceSearch.value.toLowerCase())
  )
})

// Hizmet seçimi
function selectService(service) {
  form.value.service = service.name
  form.value.cost = service.cost ?? 0
  serviceSearch.value = service.name
  isServiceListOpen.value = false  // ✅ listeyi kapat
}
  




  onMounted(async () => {
    
      //updateBusySlots()
    try {
         generateTimeSlots()
      const res = await axios.get('http://127.0.0.1:8000/api/appointments')
      customers.value = res.data.customers
      users.value = res.data.users
      appointments.value = res.data.appointments
      today.value = res.data.today
      // Hizmetler
      const serviceRes = await axios.get('http://127.0.0.1:8000/api/service')
      services.value = serviceRes.data

      console.log('Gelen veri:', res.data)
    
    
    } catch (err) {
      console.error('API Hatası:', err.response?.data || err)
      alert('Randevular yüklenirken hata oluştu!')
    }
  })

  // Saat aralıklarını oluştur
function generateTimeSlots() {
  const slots = []
  let startHour = 8, startMin = 0

  while (startHour < 20) {
    const time = `${String(startHour).padStart(2,'0')}:${String(startMin).padStart(2,'0')}`
    slots.push({ time, isBusy: false })
    startMin += 30
    if (startMin >= 60) { startHour++; startMin = 0 }
  }

  // ❗ DOM'u sıfırlamayan yöntem
  timeSlots.value.length = 0
  timeSlots.value.push(...slots)
}

  // Slotların durumunu güncelle
 function updateBusySlots() {
  const date = form.value.date
  const employeeId = form.value.employee_id

  timeSlots.value.forEach(slot => {
    slot.isBusy = appointments.value.some(a =>
      a.user_id == employeeId &&
      dayjs(a.start_datetime).isBefore(dayjs(`${date}T${slot.time}`).add(29,'minute')) &&
      dayjs(a.end_datetime).isAfter(dayjs(`${date}T${slot.time}`))
    )
  })
}

  // Slot seçimi
  function toggleSlot(index) {
    const slot = timeSlots.value[index]
    if (slot.isBusy) return
    if (selectedSlots.value.includes(index)) {
      selectedSlots.value = selectedSlots.value.filter(i => i !== index)
    } else {
      const min = Math.min(...selectedSlots.value)
      const max = Math.max(...selectedSlots.value)
      if (selectedSlots.value.length === 0 || index === min-1 || index === max+1) {
        selectedSlots.value.push(index)
      } else return alert("Sadece ardışık saatler seçilebilir!")
    }

    if (selectedSlots.value.length > 0) {
      selectedSlots.value.sort((a,b)=>a-b)
      const first = timeSlots.value[selectedSlots.value[0]]
      const last = timeSlots.value[selectedSlots.value[selectedSlots.value.length-1]]
      form.value.start_datetime = `${form.value.date}T${first.time}:00`
      let [h,m] = last.time.split(':').map(Number)
      m += 29
      if (m>=60){ m-=60; h++ }
      form.value.end_datetime = `${form.value.date}T${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`
    } else {
      form.value.start_datetime = ''
      form.value.end_datetime = ''
    }
  }

  // Form gönderimi
  async function submitAppointment() {
    try {
      await axios.post('http://127.0.0.1:8000/api/appointments', form.value)
      alert('Randevu kaydedildi!')
      // Listeyi yenile
      const res = await axios.get('http://127.0.0.1:8000/api/appointments')
      appointments.value = res.data.appointments
      updateBusySlots()
      selectedSlots.value = []
    } catch (err) {
      console.error('API Hatası:', err.response?.data || err)
      alert('Randevu kaydedilirken hata oluştu!')
    }
  }

  // Haftalık görünüm yardımcıları
  const daysOfWeek = computed(() => {
    const startOfWeek = dayjs(form.value.date).startOf('week').add(1,'day') // Pazartesi
    return Array.from({length:7}, (_,i)=>({
      label: ['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'][i],
      date: startOfWeek.add(i,'day').format('YYYY-MM-DD')
    }))
  })

  function hasAppointment(date,userId,slotTime){
    return appointments.value.some(a => a.user_id == userId &&
      dayjs(a.start_datetime).isBefore(dayjs(`${date}T${slotTime}`).add(29,'minute')) &&
      dayjs(a.end_datetime).isAfter(dayjs(`${date}T${slotTime}`)))
  }

  function getAppointment(date,userId,slotTime){
    return appointments.value.find(a => a.user_id == userId &&
      dayjs(a.start_datetime).isBefore(dayjs(`${date}T${slotTime}`).add(29,'minute')) &&
      dayjs(a.end_datetime).isAfter(dayjs(`${date}T${slotTime}`)))
  }

  function slotButtonClass(slot){
    let cls = 'p-2 rounded text-sm font-medium '
    if(slot.isBusy) cls += 'bg-red-200 cursor-not-allowed'
    else cls += 'bg-green-200 hover:bg-green-300'
    if(selectedSlots.value.some(i=>timeSlots.value[i]===slot)) cls += ' ring ring-blue-400'
    return cls
  }

watch([() => form.value.date, () => form.value.employee_id], () => {
  if (!form.value.employee_id) return; // Çalışan seçilmediyse hiçbir şey yapma
  updateBusySlots();
  selectedSlots.value = [];
});
  </script>

 

  <style scoped>
  /* Küçük stil iyileştirmeleri */
  </style>
