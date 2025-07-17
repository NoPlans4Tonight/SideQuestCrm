<template>
  <div class="min-h-screen bg-gray-100">
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
          <h1 class="text-3xl font-bold text-gray-900">Edit Job</h1>
        </div>
      </div>
    </div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
          <form v-if="loaded" @submit.prevent="updateJob">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Title -->
              <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700">Job Title</label>
                <input
                  id="title"
                  v-model="form.title"
                  type="text"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                />
                <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title[0] }}</p>
              </div>
              <!-- Customer -->
              <div>
                <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer</label>
                <select
                  id="customer_id"
                  v-model="form.customer_id"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">Select a customer</option>
                  <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                    {{ customer.full_name || (customer.first_name + ' ' + customer.last_name) }}
                  </option>
                </select>
                <p v-if="errors.customer_id" class="mt-1 text-sm text-red-600">{{ errors.customer_id[0] }}</p>
              </div>
              <!-- Status -->
              <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select
                  id="status"
                  v-model="form.status"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="scheduled">Scheduled</option>
                  <option value="in_progress">In Progress</option>
                  <option value="completed">Completed</option>
                  <option value="cancelled">Cancelled</option>
                  <option value="on_hold">On Hold</option>
                </select>
                <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status[0] }}</p>
              </div>
              <!-- Priority -->
              <div>
                <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                <select
                  id="priority"
                  v-model="form.priority"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
                <p v-if="errors.priority" class="mt-1 text-sm text-red-600">{{ errors.priority[0] }}</p>
              </div>
              <!-- Scheduled Date -->
              <div>
                <label for="scheduled_date" class="block text-sm font-medium text-gray-700">Scheduled Date</label>
                <input
                  id="scheduled_date"
                  v-model="form.scheduled_date"
                  type="date"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                />
                <p v-if="errors.scheduled_date" class="mt-1 text-sm text-red-600">{{ errors.scheduled_date[0] }}</p>
              </div>
              <!-- Estimated Hours -->
              <div>
                <label for="estimated_hours" class="block text-sm font-medium text-gray-700">Estimated Hours</label>
                <input
                  id="estimated_hours"
                  v-model="form.estimated_hours"
                  type="number"
                  step="0.5"
                  min="0"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                />
                <p v-if="errors.estimated_hours" class="mt-1 text-sm text-red-600">{{ errors.estimated_hours[0] }}</p>
              </div>
              <!-- Price -->
              <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                <input
                  id="price"
                  v-model="form.price"
                  type="number"
                  step="0.01"
                  min="0"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                />
                <p v-if="errors.price" class="mt-1 text-sm text-red-600">{{ errors.price[0] }}</p>
              </div>
              <!-- Assigned Worker -->
              <div>
                <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assign To</label>
                <select
                  id="assigned_to"
                  v-model="form.assigned_to"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">Unassigned</option>
                  <option v-for="worker in workers" :key="worker.id" :value="worker.id">
                    {{ worker.name }}
                  </option>
                </select>
                <p v-if="errors.assigned_to" class="mt-1 text-sm text-red-600">{{ errors.assigned_to[0] }}</p>
              </div>
            </div>
            <!-- Description -->
            <div class="mt-6">
              <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
              ></textarea>
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
            </div>
            <!-- Notes -->
            <div class="mt-6">
              <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
              <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
              ></textarea>
              <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes[0] }}</p>
            </div>
            <div class="flex items-center justify-end mt-6">
              <button
                type="button"
                @click="$router.push('/jobs')"
                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ loading ? 'Updating...' : 'Update Job' }}
              </button>
            </div>
            <div v-if="error" class="text-red-600 text-sm mt-2">{{ error }}</div>
          </form>
          <div v-else class="text-center py-8">Loading...</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const loaded = ref(false)
const customers = ref([])
const workers = ref([])
const errors = ref({})
const error = ref(null)
const form = ref({
  title: '',
  description: '',
  customer_id: '',
  status: 'scheduled',
  priority: 'medium',
  scheduled_date: '',
  estimated_hours: '',
  price: '',
  notes: '',
  assigned_to: ''
})

onMounted(async () => {
  await loadCustomers()
  await loadWorkers()
  await loadJob()
})

const loadCustomers = async () => {
  try {
    const response = await fetch('/api/customers', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json'
      },
      credentials: 'include'
    })
    if (response.ok) {
      const data = await response.json()
      customers.value = data.data || []
    }
  } catch (error) {
    console.error('Error loading customers:', error)
  }
}

const loadWorkers = async () => {
  try {
    const response = await fetch('/api/users', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json'
      },
      credentials: 'include'
    })
    if (response.ok) {
      const data = await response.json()
      workers.value = data.data || []
    }
  } catch (error) {
    console.error('Error loading workers:', error)
  }
}

const loadJob = async () => {
  try {
    const response = await fetch(`/api/jobs/${route.params.id}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json'
      },
      credentials: 'include'
    })
    if (response.ok) {
      const data = await response.json()
      const job = data.job || data.data
      form.value = {
        title: job.title,
        description: job.description,
        customer_id: job.customer_id,
        status: job.status,
        priority: job.priority,
        scheduled_date: job.scheduled_date,
        estimated_hours: job.estimated_hours,
        price: job.price,
        notes: job.notes,
        assigned_to: job.assigned_to || ''
      }
      loaded.value = true
    }
  } catch (err) {
    error.value = 'Failed to load job.'
  }
}

const updateJob = async () => {
  try {
    loading.value = true
    errors.value = {}
    error.value = null
    const response = await fetch(`/api/jobs/${route.params.id}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(form.value),
      credentials: 'include'
    })
    const data = await response.json()
    if (response.ok) {
      router.push('/jobs')
    } else {
      errors.value = data.errors || {}
      error.value = data.message || 'Failed to update job.'
    }
  } catch (err) {
    error.value = 'Failed to update job.'
  } finally {
    loading.value = false
  }
}
</script>
