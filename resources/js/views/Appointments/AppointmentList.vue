<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Calendar</h1>
            <p class="mt-1 text-sm text-gray-500">Schedule appointments and manage your work calendar</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="$router.push('/appointments/create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              New Appointment
            </button>

          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Calendar Controls -->
      <div class="bg-white shadow-sm rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <button
                @click="previousMonth"
                class="p-2 text-gray-400 hover:text-gray-600"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
              </button>
              <h2 class="text-xl font-semibold text-gray-900">{{ currentMonthYear }}</h2>
              <button
                @click="nextMonth"
                class="p-2 text-gray-400 hover:text-gray-600"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </button>
            </div>
            <div class="flex items-center space-x-4">
              <button
                @click="today"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              >
                Today
              </button>
              <button
                @click="loadData"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              >
                Refresh
              </button>
              <div class="flex items-center space-x-4">
                <!-- Status Legend -->
                <div class="flex items-center space-x-3">
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                    <span class="text-sm text-gray-600">Scheduled</span>
                  </div>
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-sm text-gray-600">Confirmed</span>
                  </div>
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                    <span class="text-sm text-gray-600">In Progress</span>
                  </div>
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-gray-500 rounded-full mr-2"></div>
                    <span class="text-sm text-gray-600">Completed</span>
                  </div>
                  <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                    <span class="text-sm text-gray-600">Cancelled</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="bg-white shadow-sm rounded-lg">
        <!-- Calendar Header -->
        <div class="grid grid-cols-7 gap-px bg-gray-200 border-b border-gray-200">
          <div v-for="day in weekDays" :key="day" class="bg-gray-50 px-3 py-2 text-center text-sm font-medium text-gray-500">
            {{ day }}
          </div>
        </div>

        <!-- Calendar Body -->
        <div class="grid grid-cols-7 gap-px bg-gray-200">
          <div
            v-for="day in calendarDays"
            :key="day.date"
            class="bg-white min-h-32 p-2"
            :class="{
              'bg-gray-50': !day.isCurrentMonth,
              'bg-blue-50': day.isToday
            }"
          >
            <div class="flex items-center justify-between mb-1">
              <span
                class="text-sm font-medium"
                :class="{
                  'text-gray-400': !day.isCurrentMonth,
                  'text-blue-600': day.isToday,
                  'text-gray-900': day.isCurrentMonth && !day.isToday
                }"
              >
                {{ day.dayNumber }}
              </span>
              <button
                v-if="day.isCurrentMonth"
                @click="openQuickAdd(day.date)"
                class="text-gray-400 hover:text-gray-600 text-xs"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
              </button>
            </div>

            <!-- Events for this day -->
            <div class="space-y-1">
              <!-- Appointments -->
              <div
                v-for="appointment in getAppointmentsForDay(day.date)"
                :key="`appointment-${appointment.id}`"
                @click="viewAppointment(appointment.id)"
                :class="`text-xs p-1 rounded cursor-pointer border transition-colors hover:opacity-80 ${getAppointmentStatusColor(appointment.status)}`"
              >
                <div class="flex items-center justify-between">
                  <div class="font-medium truncate flex-1">{{ appointment.title }}</div>
                  <span class="text-xs ml-1">{{ getAppointmentStatusIcon(appointment.status) }}</span>
                </div>
                <div class="text-xs opacity-75">{{ formatTime(appointment.start_time) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Add Modal -->
      <div v-if="showQuickAddModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
          <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Add - {{ formatDate(selectedDate) }}</h3>
            <div class="space-y-4">
              <button
                @click="quickAddAppointment"
                class="w-full flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Add Appointment
              </button>

            </div>
            <div class="mt-4">
              <button
                @click="showQuickAddModal = false"
                class="w-full px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAppointmentStore } from '@/stores/appointmentStore'

const router = useRouter()
const appointmentStore = useAppointmentStore()

// Calendar state
const currentDate = ref(new Date())
const showQuickAddModal = ref(false)
const selectedDate = ref(null)

// Calendar data
const appointments = ref([])

// Computed properties
const currentMonthYear = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric'
  })
})

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()

  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - firstDay.getDay())

  const days = []
  const today = new Date()

  for (let i = 0; i < 42; i++) {
    const date = new Date(startDate)
    date.setDate(startDate.getDate() + i)

    days.push({
      date: date.toISOString().split('T')[0],
      dayNumber: date.getDate(),
      isCurrentMonth: date.getMonth() === month,
      isToday: date.toDateString() === today.toDateString()
    })
  }

  return days
})

const getAppointmentStatusColor = (status) => {
  const statusColors = {
    'scheduled': 'bg-blue-100 text-blue-800 border-blue-200',
    'confirmed': 'bg-green-100 text-green-800 border-green-200',
    'in_progress': 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'completed': 'bg-gray-100 text-gray-800 border-gray-200',
    'cancelled': 'bg-red-100 text-red-800 border-red-200',
    'no_show': 'bg-orange-100 text-orange-800 border-orange-200',
    'rescheduled': 'bg-purple-100 text-purple-800 border-purple-200'
  }

  return statusColors[status] || 'bg-gray-100 text-gray-800 border-gray-200'
}

const getAppointmentStatusIcon = (status) => {
  const statusIcons = {
    'scheduled': '📅',
    'confirmed': '✅',
    'in_progress': '🔄',
    'completed': '✅',
    'cancelled': '❌',
    'no_show': '⏰',
    'rescheduled': '📝'
  }

  return statusIcons[status] || '📅'
}

// Methods
const loadData = async () => {
  try {
    // Load appointments for the current month
    const startOfMonth = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1)
    const endOfMonth = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0)

    const dateFrom = startOfMonth.toISOString().split('T')[0]
    const dateTo = endOfMonth.toISOString().split('T')[0]

    // Load appointments for the current month

    await appointmentStore.fetchAppointments({
      date_from: dateFrom,
      date_to: dateTo
    })



  } catch (error) {
    console.error('Error loading calendar data:', error)
  }
}



const getAppointmentsForDay = (date) => {
  // Use a robust date comparison that handles timezone issues
  const appointments = appointmentStore.getAppointments.filter(appointment => {
    // Parse the appointment start time and extract just the date part
    const appointmentDate = new Date(appointment.start_time)
    const appointmentDateStr = appointmentDate.toISOString().split('T')[0]

    // Also try parsing the raw string first to avoid timezone conversion issues
    const rawDateStr = appointment.start_time.split('T')[0]

    return appointmentDateStr === date || rawDateStr === date
  })

  return appointments
}



const previousMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
  loadData()
}

const nextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
  loadData()
}

const today = () => {
  currentDate.value = new Date()
  loadData()
}

const openQuickAdd = (date) => {
  selectedDate.value = date
  showQuickAddModal.value = true
}

const quickAddAppointment = () => {
  showQuickAddModal.value = false
  router.push(`/appointments/create?date=${selectedDate.value}`)
}



const viewAppointment = (id) => {
  router.push(`/appointments/${id}`)
}



const formatTime = (dateTime) => {
  if (!dateTime) return ''
  return new Date(dateTime).toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(() => {
  loadData()
})
</script>
