<template>
  <div class="p-8 max-w-3xl mx-auto bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Job Details</h1>
    <div v-if="loading" class="text-gray-500">Loading...</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>
    <div v-else>
      <div class="mb-4">
        <span class="font-semibold">Title:</span> {{ job.title }}
      </div>
      <div class="mb-4">
        <span class="font-semibold">Description:</span> {{ job.description }}
      </div>
      <div class="mb-4">
        <span class="font-semibold">Customer:</span>
        <span v-if="job.customer">
          {{ job.customer.full_name || (job.customer.first_name + ' ' + job.customer.last_name) }}
        </span>
        <span v-else>—</span>
      </div>
      <div class="mb-4">
        <span class="font-semibold">Status:</span> {{ job.status }}
      </div>
      <div class="mb-4">
        <span class="font-semibold">Priority:</span> {{ job.priority }}
      </div>
      <div class="mb-4">
        <span class="font-semibold">Scheduled Date:</span> {{ job.scheduled_date || '—' }}
      </div>
      <div class="mb-4">
        <span class="font-semibold">Estimated Hours:</span> {{ job.estimated_hours || '—' }}
      </div>
      <div class="mb-4">
        <span class="font-semibold">Price:</span> {{ job.price || '—' }}
      </div>
      <div class="mb-4">
        <span class="font-semibold">Assigned Worker:</span>
        <span v-if="job.assigned_user">
          {{ job.assigned_user.name }} <span class="text-gray-500">({{ job.assigned_user.email }})</span>
        </span>
        <span v-else>Unassigned</span>
      </div>
      <div class="mb-4">
        <span class="font-semibold">Notes:</span> {{ job.notes || '—' }}
      </div>
      <div class="mb-4">
        <span class="font-semibold">Created At:</span> {{ job.created_at ? new Date(job.created_at).toLocaleString() : '—' }}
      </div>
      <div class="mb-4">
        <span class="font-semibold">Updated At:</span> {{ job.updated_at ? new Date(job.updated_at).toLocaleString() : '—' }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const job = ref({})
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  await fetchJob()
})

const fetchJob = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await fetch(`/api/jobs/${route.params.id}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json'
      },
      credentials: 'include'
    })
    const data = await response.json()
    if (response.ok) {
      job.value = data.job || data.data || data
    } else {
      error.value = data.message || 'Failed to load job.'
    }
  } catch (err) {
    error.value = 'Failed to load job.'
  } finally {
    loading.value = false
  }
}
</script>
