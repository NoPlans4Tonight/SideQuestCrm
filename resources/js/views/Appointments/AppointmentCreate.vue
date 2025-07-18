<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <h1 class="text-3xl font-bold text-gray-900">Create Appointment</h1>
          <button
            @click="$router.push('/appointments')"
            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            Back to Appointments
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Appointment Details</h3>
        </div>

        <form @submit.prevent="createAppointment" class="p-6 space-y-6">
          <!-- Title -->
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
            <input
              id="title"
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter appointment title"
            >
          </div>

          <!-- Description -->
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter appointment description"
            ></textarea>
          </div>

          <!-- Appointment Type -->
          <div>
            <label for="appointment_type" class="block text-sm font-medium text-gray-700">Appointment Type *</label>
            <select
              id="appointment_type"
              v-model="form.appointment_type"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select appointment type</option>
              <option value="estimate">Estimate</option>
              <option value="inspection">Inspection</option>
              <option value="repair">Repair</option>
              <option value="maintenance">Maintenance</option>
              <option value="follow_up">Follow Up</option>
              <option value="other">Other</option>
            </select>
          </div>

          <!-- Customer Selection -->
          <div>
            <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer</label>
            <select
              id="customer_id"
              v-model="form.customer_id"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select customer (optional)</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                {{ customer.full_name || (customer.first_name + ' ' + customer.last_name) }}
              </option>
            </select>
          </div>

          <!-- Date and Time -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="start_time" class="block text-sm font-medium text-gray-700">Start Date & Time *</label>
              <input
                id="start_time"
                v-model="form.start_time"
                type="datetime-local"
                @change="onStartTimeChange"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              >
            </div>
            <div>
              <label for="end_time" class="block text-sm font-medium text-gray-700">End Date & Time</label>
              <input
                id="end_time"
                v-model="form.end_time"
                type="datetime-local"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              >
            </div>
          </div>

          <!-- Duration -->
          <div>
            <label for="duration" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
            <input
              id="duration"
              v-model="form.duration"
              type="number"
              min="15"
              max="480"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter duration in minutes"
            >
            <p class="mt-1 text-sm text-gray-500">Leave empty to calculate from start and end times</p>
          </div>

          <!-- Status -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
            <select
              id="status"
              v-model="form.status"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="scheduled">Scheduled</option>
              <option value="confirmed">Confirmed</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
              <option value="no_show">No Show</option>
            </select>
          </div>

          <!-- Assigned To -->
          <div>
            <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assigned To</label>
            <select
              id="assigned_to"
              v-model="form.assigned_to"
              @change="onUserChange"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select user (optional)</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>

            <!-- User Schedule Preview -->
            <div v-if="selectedUserSchedule && form.assigned_to" class="mt-4 p-4 bg-gray-50 rounded-lg">
              <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-medium text-gray-900">
                  {{ selectedUserSchedule.user.name }}'s Schedule
                </h4>
                <button
                  @click="refreshUserSchedule"
                  class="text-blue-600 hover:text-blue-800 text-sm"
                >
                  Refresh
                </button>
              </div>

              <!-- Date Navigation -->
              <div class="flex items-center space-x-2 mb-3">
                <button
                  type="button"
                  @click="previousScheduleDate"
                  class="p-1 text-gray-400 hover:text-gray-600"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                  </svg>
                </button>
                <span class="text-sm font-medium text-gray-700">
                  {{ formatScheduleDate(scheduleDate) }}
                </span>
                <button
                  type="button"
                  @click="nextScheduleDate"
                  class="p-1 text-gray-400 hover:text-gray-600"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>
              </div>

              <!-- Schedule Items -->
              <div class="space-y-2">
                <div v-if="selectedUserSchedule.appointments.length === 0" class="text-sm text-gray-500">
                  No scheduled items for this date
                </div>

                <!-- Appointments -->
                <div v-for="appointment in selectedUserSchedule.appointments" :key="`appointment-${appointment.id}`" class="flex items-center p-2 bg-blue-100 rounded">
                  <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                  <div class="flex-1">
                    <div class="text-sm font-medium text-blue-900">{{ appointment.title }}</div>
                    <div class="text-xs text-blue-700">
                      {{ formatTime(appointment.start_time) }} - {{ formatTime(appointment.end_time) }}
                      <span v-if="appointment.customer"> • {{ appointment.customer.name }}</span>
                    </div>
                  </div>
                  <span class="text-xs text-blue-600 px-2 py-1 bg-blue-200 rounded">{{ appointment.status }}</span>
                </div>


              </div>

              <!-- Conflict Warning -->
              <div v-if="hasScheduleConflict" class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded">
                <div class="flex items-center">
                  <svg class="w-4 h-4 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <span class="text-sm text-yellow-800">Potential schedule conflict detected</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Location -->
          <div>
            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
            <input
              id="location"
              v-model="form.location"
              type="text"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter appointment location"
            >
          </div>

          <!-- Notes -->
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="3"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter additional notes"
            ></textarea>
          </div>

          <!-- Error Message -->
          <div v-if="appointmentStore.getError" class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Error</h3>
                <div class="mt-2 text-sm text-red-700">
                  {{ appointmentStore.getError }}
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="$router.push('/appointments')"
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="appointmentStore.isLoading"
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="appointmentStore.isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ appointmentStore.isLoading ? 'Creating...' : 'Create Appointment' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAppointmentStore } from '@/stores/appointmentStore'
import { useCustomerStore } from '@/stores/customerStore'

const router = useRouter()
const appointmentStore = useAppointmentStore()
const customerStore = useCustomerStore()

const customers = ref([])
const users = ref([])
const selectedUserSchedule = ref(null)
const scheduleDate = ref(new Date().toLocaleDateString('en-CA')) // YYYY-MM-DD format
const hasScheduleConflict = ref(false)

const form = ref({
  title: '',
  description: '',
  appointment_type: '',
  customer_id: '',
  start_time: '',
  end_time: '',
  duration: '',
  status: 'scheduled',
  assigned_to: '',
  location: '',
  notes: ''
})

onMounted(async () => {
  await loadCustomers()
  await loadUsers()
  setDefaultDateTime()
})

const loadCustomers = async () => {
  try {
    await customerStore.fetchCustomers({ per_page: 100 })
    customers.value = customerStore.getCustomers
  } catch (error) {
    console.error('Error loading customers:', error)
  }
}

const loadUsers = async () => {
  try {
    const token = localStorage.getItem('auth_token')
    const response = await fetch('/api/users', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      users.value = data.data || []
    }
  } catch (error) {
    console.error('Error loading users:', error)
  }
}

const setDefaultDateTime = () => {
  const now = new Date()
  const tomorrow = new Date(now)
  tomorrow.setDate(tomorrow.getDate() + 1)
  tomorrow.setHours(9, 0, 0, 0) // 9 AM tomorrow

  // Format datetime-local input in local timezone
  const formatLocalDateTime = (date) => {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')
    return `${year}-${month}-${day}T${hours}:${minutes}`
  }

  form.value.start_time = formatLocalDateTime(tomorrow)

  const endTime = new Date(tomorrow)
  endTime.setHours(10, 0, 0, 0) // 10 AM tomorrow
  form.value.end_time = formatLocalDateTime(endTime)

  form.value.duration = '60' // 1 hour default

  // Set schedule date to match the appointment date using local date formatting
  scheduleDate.value = tomorrow.toLocaleDateString('en-CA') // YYYY-MM-DD format
}

const onUserChange = async () => {
  if (form.value.assigned_to) {
    await loadUserSchedule()
  } else {
    selectedUserSchedule.value = null
    hasScheduleConflict.value = false
  }
}

const onStartTimeChange = () => {
  if (form.value.start_time && form.value.assigned_to) {
    // Extract date part from datetime-local input and update schedule date
    const [datePart] = form.value.start_time.split('T')
    scheduleDate.value = datePart
    if (selectedUserSchedule.value) {
      loadUserSchedule()
    }
  }
}

const loadUserSchedule = async () => {
  if (!form.value.assigned_to) return

  try {
    const token = localStorage.getItem('auth_token')
    const response = await fetch(`/api/users/${form.value.assigned_to}/schedule?date=${scheduleDate.value}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      selectedUserSchedule.value = data
      checkForConflicts()
    }
  } catch (error) {
    console.error('Error loading user schedule:', error)
  }
}

const refreshUserSchedule = async () => {
  await loadUserSchedule()
}

const previousScheduleDate = () => {
  // Parse the date string manually to avoid timezone issues
  const [year, month, day] = scheduleDate.value.split('-').map(Number)
  const date = new Date(year, month - 1, day - 1) // month is 0-indexed, subtract 1 day
  scheduleDate.value = date.toLocaleDateString('en-CA') // YYYY-MM-DD format
  loadUserSchedule()
}

const nextScheduleDate = () => {
  // Parse the date string manually to avoid timezone issues
  const [year, month, day] = scheduleDate.value.split('-').map(Number)
  const date = new Date(year, month - 1, day + 1) // month is 0-indexed, add 1 day
  scheduleDate.value = date.toLocaleDateString('en-CA') // YYYY-MM-DD format
  loadUserSchedule()
}

const checkForConflicts = () => {
  if (!selectedUserSchedule.value || !form.value.start_time || !form.value.end_time) {
    hasScheduleConflict.value = false
    return
  }

  const appointmentStart = new Date(form.value.start_time)
  const appointmentEnd = new Date(form.value.end_time)
  const appointmentDate = appointmentStart.toLocaleDateString('en-CA') // YYYY-MM-DD format

  // Only check conflicts for the same date
  if (appointmentDate !== scheduleDate.value) {
    hasScheduleConflict.value = false
    return
  }

  // Check for conflicts with appointments
  const hasAppointmentConflict = selectedUserSchedule.value.appointments.some(appointment => {
    const existingStart = new Date(appointment.start_time)
    const existingEnd = new Date(appointment.end_time)

    return (
      (appointmentStart < existingEnd && appointmentEnd > existingStart) ||
      (existingStart < appointmentEnd && existingEnd > appointmentStart)
    )
  })

  hasScheduleConflict.value = hasAppointmentConflict
}

const formatTime = (dateTime) => {
  if (!dateTime) return ''
  return new Date(dateTime).toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })
}

const formatScheduleDate = (date) => {
  if (!date) return ''

  // Parse the date string manually to avoid timezone issues
  const [year, month, day] = date.split('-').map(Number)
  const dateObj = new Date(year, month - 1, day) // month is 0-indexed

  return dateObj.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  })
}



const createAppointment = async () => {
  try {
    appointmentStore.clearError()

    const appointmentData = {
      ...form.value,
      customer_id: form.value.customer_id || null,
      assigned_to: form.value.assigned_to || null,
      duration: form.value.duration ? parseInt(form.value.duration) : null
    }

    await appointmentStore.createAppointment(appointmentData)
    router.push('/appointments')
  } catch (error) {
    console.error('Error creating appointment:', error)
  }
}

// Watchers for automatic conflict detection
watch([() => form.value.start_time, () => form.value.end_time], () => {
  if (selectedUserSchedule.value) {
    checkForConflicts()
  }
})

// Watcher for start_time changes to update schedule date
watch(() => form.value.start_time, (newValue) => {
  if (newValue && form.value.assigned_to) {
    const [datePart] = newValue.split('T')
    scheduleDate.value = datePart
    if (selectedUserSchedule.value) {
      loadUserSchedule()
    }
  }
})

// Watcher for assigned_to changes to sync schedule date with appointment date
watch(() => form.value.assigned_to, (newValue) => {
  if (newValue && form.value.start_time) {
    // Update schedule date to match appointment date when user changes
    const [datePart] = form.value.start_time.split('T')
    scheduleDate.value = datePart
    loadUserSchedule()
  }
})

// Add watcher for scheduleDate changes to reload schedule
watch(() => scheduleDate.value, () => {
  if (form.value.assigned_to) {
    loadUserSchedule()
  }
})
</script>
