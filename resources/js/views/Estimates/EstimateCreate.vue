<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Create Estimate</h1>
        <p class="text-gray-600 mt-2">Create a new estimate for your customer</p>
      </div>
      <router-link
        to="/estimates"
        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Estimates
      </router-link>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Basic Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Customer *</label>
            <select
              v-model="form.customer_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select a customer</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                {{ customer.full_name || 'Unnamed Customer' }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Lead (Optional)</label>
            <select
              v-model="form.lead_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select a lead</option>
              <option v-for="lead in leads" :key="lead.id" :value="lead.id">
                {{ lead.full_name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
            <input
              v-model="form.title"
              type="text"
              required
              placeholder="Estimate title"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
            <select
              v-model="form.status"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="draft">Draft</option>
              <option value="pending">Pending</option>
              <option value="sent">Sent</option>
              <option value="accepted">Accepted</option>
              <option value="rejected">Rejected</option>
              <option value="expired">Expired</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Valid Until</label>
            <input
              v-model="form.valid_until"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Assigned To</label>
            <select
              v-model="form.assigned_to"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Select a user</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            placeholder="Estimate description"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          ></textarea>
        </div>

        <!-- Tax and Discount -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
            <input
              v-model="form.tax_rate"
              type="number"
              step="0.01"
              min="0"
              max="100"
              placeholder="0.00"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Discount Amount</label>
            <input
              v-model="form.discount_amount"
              type="number"
              step="0.01"
              min="0"
              placeholder="0.00"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Total Amount</label>
            <div class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 font-medium">
              ${{ formatCurrency(calculatedTotal) }}
            </div>
          </div>
        </div>

        <!-- Notes and Terms -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <textarea
              v-model="form.notes"
              rows="4"
              placeholder="Additional notes"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Terms & Conditions</label>
            <textarea
              v-model="form.terms_conditions"
              rows="4"
              placeholder="Terms and conditions"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
          </div>
        </div>

        <!-- Estimate Items -->
        <div>
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Estimate Items</h3>
            <button
              type="button"
              @click="addItem"
              class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg text-sm flex items-center gap-1"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Add Item
            </button>
          </div>

          <div v-if="form.estimate_items.length === 0" class="text-center py-8 text-gray-500">
            No items added yet. Click "Add Item" to start.
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="(item, index) in form.estimate_items"
              :key="index"
              class="border border-gray-200 rounded-lg p-4"
            >
              <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                  <input
                    v-model="item.description"
                    type="text"
                    required
                    placeholder="Item description"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                  <select
                    v-model="item.service_id"
                    @change="updateItemFromService(index)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">Select service</option>
                    <option v-for="service in services" :key="service.id" :value="service.id">
                      {{ service.name }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                  <input
                    v-model="item.quantity"
                    type="number"
                    step="0.01"
                    min="0"
                    @input="calculateItemTotal(index)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
                  <input
                    v-model="item.unit_price"
                    type="number"
                    step="0.01"
                    min="0"
                    @input="calculateItemTotal(index)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div class="flex items-end gap-2">
                  <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                    <div class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900">
                      ${{ formatCurrency(item.total_price) }}
                    </div>
                  </div>
                  <button
                    type="button"
                    @click="removeItem(index)"
                    class="text-red-600 hover:text-red-800 p-2"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </div>

              <div class="mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <input
                  v-model="item.notes"
                  type="text"
                  placeholder="Item notes"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Totals Summary -->
        <div v-if="form.estimate_items.length > 0" class="bg-gray-50 rounded-lg p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-medium">${{ formatCurrency(calculatedSubtotal) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Tax ({{ form.tax_rate || 0 }}%):</span>
                <span class="font-medium">${{ formatCurrency(calculatedTax) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Discount:</span>
                <span class="font-medium">-${{ formatCurrency(form.discount_amount || 0) }}</span>
              </div>
            </div>
            <div class="border-t pt-2">
              <div class="flex justify-between text-lg font-bold">
                <span>Total:</span>
                <span>${{ formatCurrency(calculatedTotal) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-4 pt-6 border-t">
          <router-link
            to="/estimates"
            class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors"
          >
            Cancel
          </router-link>
          <button
            type="submit"
            :disabled="isLoading"
            class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2"
          >
            <div v-if="isLoading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            {{ isLoading ? 'Creating...' : 'Create Estimate' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useEstimateStore } from '@/stores/estimateStore'
import { useCustomerStore } from '@/stores/customerStore'
import { useServiceStore } from '@/stores/serviceStore'
import axios from 'axios'

const router = useRouter()
const estimateStore = useEstimateStore()
const customerStore = useCustomerStore()
const serviceStore = useServiceStore()

// Reactive data
const form = ref({
  customer_id: '',
  lead_id: '',
  title: '',
  description: '',
  status: 'draft',
  valid_until: '',
  tax_rate: 0,
  discount_amount: 0,
  notes: '',
  terms_conditions: '',
  assigned_to: '',
  estimate_items: []
})

const customers = ref([])
const leads = ref([])
const users = ref([])
const services = ref([])

// Computed properties
const isLoading = computed(() => estimateStore.isLoading)
const getError = computed(() => estimateStore.getError)

const calculatedSubtotal = computed(() => {
  return form.value.estimate_items.reduce((sum, item) => {
    return sum + (parseFloat(item.total_price) || 0)
  }, 0)
})

const calculatedTax = computed(() => {
  return calculatedSubtotal.value * (parseFloat(form.value.tax_rate) / 100)
})

const calculatedTotal = computed(() => {
  return calculatedSubtotal.value + calculatedTax.value - (parseFloat(form.value.discount_amount) || 0)
})

// Methods
const fetchCustomers = async () => {
  try {
    const response = await axios.get('/api/customers?simple=true')
    console.log('Customers data:', response.data.data)
    customers.value = response.data.data || []
  } catch (error) {
    console.error('Error fetching customers:', error)
    customers.value = []
  }
}

const fetchLeads = async () => {
  try {
    const response = await axios.get('/api/leads')
    leads.value = response.data.data
  } catch (error) {
    console.error('Error fetching leads:', error)
  }
}

const fetchUsers = async () => {
  try {
    const response = await axios.get('/api/users')
    users.value = response.data.data
  } catch (error) {
    console.error('Error fetching users:', error)
  }
}

const fetchServices = async () => {
  try {
    const response = await axios.get('/api/services')
    services.value = response.data.data
  } catch (error) {
    console.error('Error fetching services:', error)
  }
}

const addItem = () => {
  form.value.estimate_items.push({
    service_id: '',
    description: '',
    quantity: 1,
    unit_price: 0,
    total_price: 0,
    notes: '',
    sort_order: form.value.estimate_items.length + 1
  })
}

const removeItem = (index) => {
  form.value.estimate_items.splice(index, 1)
  // Update sort order
  form.value.estimate_items.forEach((item, idx) => {
    item.sort_order = idx + 1
  })
}

const calculateItemTotal = (index) => {
  const item = form.value.estimate_items[index]
  const quantity = parseFloat(item.quantity) || 0
  const unitPrice = parseFloat(item.unit_price) || 0
  item.total_price = quantity * unitPrice
}

const updateItemFromService = (index) => {
  const item = form.value.estimate_items[index]
  const service = services.value.find(s => s.id == item.service_id)

  if (service) {
    item.description = service.name
    item.unit_price = service.base_price || 0
    calculateItemTotal(index)
  }
}

const handleSubmit = async () => {
  try {
    // Validate required fields
    if (!form.value.customer_id || !form.value.title) {
      alert('Please fill in all required fields')
      return
    }

    if (form.value.estimate_items.length === 0) {
      alert('Please add at least one estimate item')
      return
    }

    // Validate estimate items
    for (const item of form.value.estimate_items) {
      if (!item.description) {
        alert('Please fill in description for all items')
        return
      }
    }

    await estimateStore.createEstimate(form.value)
    router.push('/estimates')
  } catch (error) {
    console.error('Error creating estimate:', error)
  }
}

const formatCurrency = (amount) => {
  return parseFloat(amount || 0).toFixed(2)
}

// Lifecycle
onMounted(() => {
  fetchCustomers()
  fetchLeads()
  fetchUsers()
  fetchServices()
  addItem() // Add initial item
})
</script>
