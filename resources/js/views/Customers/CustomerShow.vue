<template>
  <div class="customer-show">
    <div class="max-w-4xl mx-auto py-6">
      <!-- Loading State -->
      <div v-if="customerStore.isLoading" class="bg-white shadow rounded-lg p-6">
        <div class="animate-pulse">
          <div class="h-8 bg-gray-200 rounded w-1/4 mb-6"></div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
              <div class="h-4 bg-gray-200 rounded w-1/2"></div>
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-4 bg-gray-200 rounded w-2/3"></div>
            </div>
            <div class="space-y-3">
              <div class="h-4 bg-gray-200 rounded w-1/2"></div>
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-4 bg-gray-200 rounded w-2/3"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="customerStore.getError" class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error</h3>
            <div class="mt-2 text-sm text-red-700">
              {{ customerStore.getError }}
            </div>
          </div>
        </div>
      </div>

      <!-- Customer Data -->
      <div v-else-if="customerStore.getCurrentCustomer" class="bg-white shadow rounded-lg p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Customer Details</h1>
          <div class="flex space-x-3">
            <button
              type="button"
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              @click="$router.push('/customers')"
            >
              Back to List
            </button>
            <button
              type="button"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              @click="$router.push(`/customers/${customerStore.getCurrentCustomer.id}/edit`)"
            >
              Edit Customer
            </button>
          </div>
        </div>

        <!-- Customer Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                <dd class="text-sm text-gray-900">
                  {{ customerStore.getCurrentCustomer.first_name }} {{ customerStore.getCurrentCustomer.last_name }}
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="text-sm text-gray-900">{{ customerStore.getCurrentCustomer.email || 'No email' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                <dd class="text-sm text-gray-900">{{ customerStore.getCurrentCustomer.phone || 'No phone' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="text-sm">
                  <span :class="getStatusClass(customerStore.getCurrentCustomer.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ customerStore.getCurrentCustomer.status || 'Unknown' }}
                  </span>
                </dd>
              </div>
            </dl>
          </div>

          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Address</dt>
                <dd class="text-sm text-gray-900">{{ customerStore.getCurrentCustomer.address || 'No address' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">City</dt>
                <dd class="text-sm text-gray-900">{{ customerStore.getCurrentCustomer.city || 'No city' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">State</dt>
                <dd class="text-sm text-gray-900">{{ customerStore.getCurrentCustomer.state || 'No state' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">ZIP Code</dt>
                <dd class="text-sm text-gray-900">{{ customerStore.getCurrentCustomer.zip_code || 'No ZIP code' }}</dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Notes Section -->
        <div v-if="customerStore.getCurrentCustomer.notes" class="mt-8">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-700">{{ customerStore.getCurrentCustomer.notes }}</p>
          </div>
        </div>

        <!-- Related Data -->
        <div v-if="customerData && customerData.related_data" class="mt-8">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Related Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Estimates -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Estimates</h4>
              <div v-if="customerData.related_data.estimates.has_estimates" class="space-y-2">
                <p class="text-sm text-gray-600">Total: {{ customerData.related_data.estimates.total_count }}</p>
                <p class="text-sm text-gray-600">Pending: ${{ customerData.related_data.estimates.pending_value }}</p>
              </div>
              <p v-else class="text-sm text-gray-500">No estimates found</p>
            </div>

            <!-- Appointments -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Appointments</h4>
              <div v-if="customerData.related_data.appointments.has_appointments" class="space-y-2">
                <p class="text-sm text-gray-600">Total: {{ customerData.related_data.appointments.total_count }}</p>
                <p class="text-sm text-gray-600">Upcoming: {{ customerData.related_data.appointments.upcoming_count }}</p>
              </div>
              <p v-else class="text-sm text-gray-500">No appointments found</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Not Found State -->
      <div v-else class="bg-white shadow rounded-lg p-6">
        <div class="text-center">
          <h3 class="text-lg font-medium text-gray-900">Customer not found</h3>
          <p class="text-sm text-gray-500 mt-2">The customer you're looking for doesn't exist.</p>
          <button
            type="button"
            class="mt-4 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            @click="$router.push('/customers')"
          >
            Back to Customers
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useCustomerStore } from '@/stores/customerStore';

const route = useRoute();
const customerStore = useCustomerStore();
const customerData = ref(null);

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    prospect: 'bg-yellow-100 text-yellow-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(async () => {
  try {
    const customerId = route.params.id;
    const data = await customerStore.fetchCustomer(customerId);
    customerData.value = data;
  } catch (error) {
    console.error('Failed to fetch customer:', error);
  }
});
</script>
