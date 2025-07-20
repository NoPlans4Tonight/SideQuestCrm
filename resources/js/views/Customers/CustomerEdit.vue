<template>
  <div class="customer-edit">
    <div class="max-w-4xl mx-auto py-6">
      <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Edit Customer</h1>
          <button
            type="button"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            @click="$router.push('/customers')"
            :disabled="loading"
          >
            Cancel
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading && !customer" class="flex justify-center items-center py-12">
          <div class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-600">Loading customer...</span>
          </div>
        </div>

        <!-- Error Alert -->
        <div v-if="error" class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Error</h3>
              <div class="mt-2 text-sm text-red-700">{{ error }}</div>
            </div>
          </div>
        </div>

        <!-- Validation Errors -->
        <div v-if="validationErrors.length > 0" class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
              <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                <li v-for="error in validationErrors" :key="error">{{ error }}</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Edit Form -->
        <form v-if="customer" @submit.prevent="updateCustomer" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">First Name *</label>
              <input
                type="text"
                v-model="form.first_name"
                :class="[
                  'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500',
                  hasError('first_name') ? 'border-red-300' : ''
                ]"
                placeholder="Enter first name"
                required
              />
              <p v-if="hasError('first_name')" class="mt-1 text-sm text-red-600">{{ getError('first_name') }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Last Name *</label>
              <input
                type="text"
                v-model="form.last_name"
                :class="[
                  'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500',
                  hasError('last_name') ? 'border-red-300' : ''
                ]"
                placeholder="Enter last name"
                required
              />
              <p v-if="hasError('last_name')" class="mt-1 text-sm text-red-600">{{ getError('last_name') }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input
              type="email"
              v-model="form.email"
              :class="[
                'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500',
                hasError('email') ? 'border-red-300' : ''
              ]"
              placeholder="Enter email address"
            />
            <p v-if="hasError('email')" class="mt-1 text-sm text-red-600">{{ getError('email') }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Phone</label>
            <input
              type="tel"
              v-model="form.phone"
              :class="[
                'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500',
                hasError('phone') ? 'border-red-300' : ''
              ]"
              placeholder="Enter phone number"
            />
            <p v-if="hasError('phone')" class="mt-1 text-sm text-red-600">{{ getError('phone') }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Address</label>
            <textarea
              v-model="form.address"
              :class="[
                'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500',
                hasError('address') ? 'border-red-300' : ''
              ]"
              rows="3"
              placeholder="Enter address"
            ></textarea>
            <p v-if="hasError('address')" class="mt-1 text-sm text-red-600">{{ getError('address') }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">City</label>
              <input
                type="text"
                v-model="form.city"
                :class="[
                  'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500',
                  hasError('city') ? 'border-red-300' : ''
                ]"
                placeholder="Enter city"
              />
              <p v-if="hasError('city')" class="mt-1 text-sm text-red-600">{{ getError('city') }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">State</label>
              <input
                type="text"
                v-model="form.state"
                :class="[
                  'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500',
                  hasError('state') ? 'border-red-300' : ''
                ]"
                placeholder="Enter state"
              />
              <p v-if="hasError('state')" class="mt-1 text-sm text-red-600">{{ getError('state') }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">ZIP Code</label>
              <input
                type="text"
                v-model="form.zip_code"
                :class="[
                  'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500',
                  hasError('zip_code') ? 'border-red-300' : ''
                ]"
                placeholder="Enter ZIP code"
              />
              <p v-if="hasError('zip_code')" class="mt-1 text-sm text-red-600">{{ getError('zip_code') }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea
              v-model="form.notes"
              :class="[
                'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500',
                hasError('notes') ? 'border-red-300' : ''
              ]"
              rows="4"
              placeholder="Enter customer notes"
            ></textarea>
            <p v-if="hasError('notes')" class="mt-1 text-sm text-red-600">{{ getError('notes') }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select
              v-model="form.status"
              :class="[
                'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500',
                hasError('status') ? 'border-red-300' : ''
              ]"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="prospect">Prospect</option>
            </select>
            <p v-if="hasError('status')" class="mt-1 text-sm text-red-600">{{ getError('status') }}</p>
          </div>
        </form>

        <!-- Action Buttons -->
        <div v-if="customer" class="mt-8 flex justify-end space-x-3">
          <button
            type="button"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            @click="$router.push('/customers')"
            :disabled="loading"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            @click="updateCustomer"
            :disabled="loading"
          >
            <span v-if="loading" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Updating...
            </span>
            <span v-else>Update Customer</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCustomerStore } from '@/stores/customerStore'

const route = useRoute()
const router = useRouter()
const customerStore = useCustomerStore()

// Form data
const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state: '',
  zip_code: '',
  notes: '',
  status: 'active'
})

// Validation errors
const validationErrors = ref([])

// Computed properties
const loading = computed(() => customerStore.isLoading)
const error = computed(() => customerStore.getError)
const customer = computed(() => customerStore.getCurrentCustomer)

// Watch for customer data changes and update form
watch(customer, (newCustomer) => {
  if (newCustomer) {
    form.value = {
      first_name: newCustomer.first_name || '',
      last_name: newCustomer.last_name || '',
      email: newCustomer.email || '',
      phone: newCustomer.phone || '',
      address: newCustomer.address || '',
      city: newCustomer.city || '',
      state: newCustomer.state || '',
      zip_code: newCustomer.zip_code || '',
      notes: newCustomer.notes || '',
      status: newCustomer.status || 'active'
    }
  }
}, { immediate: true })

// Methods
const hasError = (field) => {
  return validationErrors.value.some(error => error.includes(field))
}

const getError = (field) => {
  const error = validationErrors.value.find(error => error.includes(field))
  return error || ''
}

const validateForm = () => {
  validationErrors.value = []

  if (!form.value.first_name.trim()) {
    validationErrors.value.push('First name is required')
  }

  if (!form.value.last_name.trim()) {
    validationErrors.value.push('Last name is required')
  }

  if (form.value.email && !isValidEmail(form.value.email)) {
    validationErrors.value.push('Please provide a valid email address')
  }

  return validationErrors.value.length === 0
}

const isValidEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

const loadCustomer = async () => {
  try {
    await customerStore.fetchCustomer(route.params.id)
  } catch (err) {
    // Handle 404 or other errors
    if (err.response?.status === 404) {
      router.push({
        path: '/customers',
        query: {
          message: 'Customer not found',
          type: 'error'
        }
      })
    }
  }
}

const updateCustomer = async () => {
  // Clear previous errors
  customerStore.clearError()
  validationErrors.value = []

  // Validate form
  if (!validateForm()) {
    return
  }

  try {
    // Prepare data - only include non-empty fields
    const customerData = {}
    Object.keys(form.value).forEach(key => {
      if (form.value[key] !== '' && form.value[key] !== null) {
        customerData[key] = form.value[key]
      }
    })

    await customerStore.updateCustomer(route.params.id, customerData)

    // Show success message and redirect
    router.push({
      path: '/customers',
      query: {
        message: 'Customer updated successfully',
        type: 'success'
      }
    })
  } catch (err) {
    // Handle validation errors from API
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      Object.keys(errors).forEach(field => {
        validationErrors.value.push(`${field}: ${errors[field][0]}`)
      })
    }
  }
}

// Load customer data on mount
onMounted(() => {
  if (route.params.id) {
    loadCustomer()
  }
})
</script>
