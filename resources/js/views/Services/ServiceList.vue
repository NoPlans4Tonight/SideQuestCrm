<template>
  <div class="service-list">
    <div class="max-w-4xl mx-auto py-6">
      <div class="bg-white shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Services</h1>
        <div class="mb-4 flex justify-end">
          <button @click="$router.push('/services/create')"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
            Add Service
          </button>
        </div>
        <div v-if="loading" class="text-center py-8">Loading...</div>
        <div v-else>
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Base Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hourly Rate</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3"></th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="service in services" :key="service.id">
                <td class="px-6 py-4 whitespace-nowrap">{{ service.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ service.category || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">${{ service.base_price }}</td>
                <td class="px-6 py-4 whitespace-nowrap">${{ service.hourly_rate }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="service.is_active ? 'text-green-600' : 'text-gray-400'">
                    {{ service.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button @click="$router.push(`/services/${service.id}/edit`)"
                          class="text-blue-600 hover:text-blue-900 mr-2">Edit</button>
                  <button @click="deleteService(service.id)"
                          class="text-red-600 hover:text-red-900">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useServiceStore } from '@/stores/serviceStore'

const serviceStore = useServiceStore()
const services = ref([])
const loading = ref(true)

const loadServices = async () => {
  loading.value = true
  try {
    await serviceStore.fetchServices()
    services.value = serviceStore.getServices
  } finally {
    loading.value = false
  }
}

const deleteService = async (id) => {
  if (confirm('Are you sure you want to delete this service?')) {
    await serviceStore.deleteService(id)
    await loadServices()
  }
}

onMounted(loadServices)
</script>
