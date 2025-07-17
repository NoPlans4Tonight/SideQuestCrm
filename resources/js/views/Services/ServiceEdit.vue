<template>
  <div class="service-edit">
    <div class="max-w-2xl mx-auto py-6">
      <div class="bg-white shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Service</h1>
        <form v-if="loaded" @submit.prevent="submit">
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name</label>
              <input v-model="form.name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <textarea v-model="form.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Category</label>
              <input v-model="form.category" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Base Price</label>
                <input v-model.number="form.base_price" type="number" min="0" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Hourly Rate</label>
                <input v-model.number="form.hourly_rate" type="number" min="0" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Active</label>
              <input v-model="form.is_active" type="checkbox" class="mt-1" />
            </div>
            <div class="flex justify-end">
              <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                Update Service
              </button>
            </div>
            <div v-if="error" class="text-red-600 text-sm mt-2">{{ error }}</div>
          </div>
        </form>
        <div v-else class="text-center py-8">Loading...</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useServiceStore } from '@/stores/serviceStore'

const route = useRoute()
const router = useRouter()
const serviceStore = useServiceStore()
const form = ref({
  name: '',
  description: '',
  category: '',
  base_price: 0,
  hourly_rate: 0,
  is_active: true
})
const error = ref(null)
const loaded = ref(false)

const loadService = async () => {
  error.value = null
  try {
    const service = await serviceStore.fetchService(route.params.id)
    form.value = {
      name: service.name,
      description: service.description,
      category: service.category,
      base_price: service.base_price,
      hourly_rate: service.hourly_rate,
      is_active: service.is_active
    }
    loaded.value = true
  } catch (err) {
    error.value = serviceStore.getError
  }
}

const submit = async () => {
  error.value = null
  try {
    await serviceStore.updateService(route.params.id, form.value)
    router.push('/services')
  } catch (err) {
    error.value = serviceStore.getError
  }
}

onMounted(loadService)
</script>
