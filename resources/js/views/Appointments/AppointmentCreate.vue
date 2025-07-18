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
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select user (optional)</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAppointmentStore } from '@/stores/appointmentStore'
import { useCustomerStore } from '@/stores/customerStore'

const router = useRouter()
const appointmentStore = useAppointmentStore()
const customerStore = useCustomerStore()

const customers = ref([])
const users = ref([])

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

  form.value.start_time = tomorrow.toISOString().slice(0, 16)

  const endTime = new Date(tomorrow)
  endTime.setHours(10, 0, 0, 0) // 10 AM tomorrow
  form.value.end_time = endTime.toISOString().slice(0, 16)

  form.value.duration = '60' // 1 hour default
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
</script>
