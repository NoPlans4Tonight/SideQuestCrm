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
          >
            Cancel
          </button>
        </div>

        <!-- Edit Form -->
        <form class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">First Name</label>
              <input
                type="text"
                v-model="customer.first_name"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter first name"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Last Name</label>
              <input
                type="text"
                v-model="customer.last_name"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter last name"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input
              type="email"
              v-model="customer.email"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter email address"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Phone</label>
            <input
              type="tel"
              v-model="customer.phone"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter phone number"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Address</label>
            <textarea
              v-model="customer.address"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              rows="3"
              placeholder="Enter address"
            ></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">City</label>
              <input
                type="text"
                v-model="customer.city"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter city"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">State</label>
              <input
                type="text"
                v-model="customer.state"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter state"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">ZIP Code</label>
              <input
                type="text"
                v-model="customer.zip_code"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter ZIP code"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea
              v-model="customer.notes"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              rows="4"
              placeholder="Enter customer notes"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select
              v-model="customer.status"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="prospect">Prospect</option>
            </select>
          </div>
        </form>

        <!-- Action Buttons -->
        <div class="mt-8 flex justify-end space-x-3">
          <button
            type="button"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            @click="$router.push('/customers')"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            @click="updateCustomer"
          >
            Update Customer
          </button>
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

// Placeholder customer data
const customer = ref({
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

// Load customer data (placeholder)
onMounted(() => {
  // In a real implementation, you would fetch customer data here
  // based on route.params.id
  console.log('Loading customer ID:', route.params.id)
})

// Update customer (placeholder)
const updateCustomer = () => {
  // In a real implementation, you would send the updated data to your API
  console.log('Updating customer:', customer.value)
  router.push('/customers')
}
</script>
