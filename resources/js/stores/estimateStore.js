import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useEstimateStore = defineStore('estimate', () => {
  // State
  const estimates = ref([])
  const currentEstimate = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })

  // Getters
  const getEstimates = computed(() => estimates.value)
  const getCurrentEstimate = computed(() => currentEstimate.value)
  const isLoading = computed(() => loading.value)
  const getError = computed(() => error.value)
  const getPagination = computed(() => pagination.value)

  // Actions
  const fetchEstimates = async (page = 1, perPage = 15) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/estimates?page=${page}&per_page=${perPage}`)
      estimates.value = response.data.data
      pagination.value = response.data.pagination
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch estimates'
      console.error('Error fetching estimates:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchEstimate = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/estimates/${id}`)
      currentEstimate.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch estimate'
      console.error('Error fetching estimate:', err)
    } finally {
      loading.value = false
    }
  }

  const createEstimate = async (estimateData) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post('/api/estimates', estimateData)
      estimates.value.unshift(response.data.data)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create estimate'
      console.error('Error creating estimate:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateEstimate = async (id, estimateData) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.put(`/api/estimates/${id}`, estimateData)
      const updatedEstimate = response.data.data

      // Update in estimates array
      const index = estimates.value.findIndex(e => e.id === id)
      if (index !== -1) {
        estimates.value[index] = updatedEstimate
      }

      // Update current estimate if it's the same
      if (currentEstimate.value && currentEstimate.value.id === id) {
        currentEstimate.value = updatedEstimate
      }

      return updatedEstimate
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update estimate'
      console.error('Error updating estimate:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteEstimate = async (id) => {
    loading.value = true
    error.value = null

    try {
      await axios.delete(`/api/estimates/${id}`)

      // Remove from estimates array
      estimates.value = estimates.value.filter(e => e.id !== id)

      // Clear current estimate if it's the same
      if (currentEstimate.value && currentEstimate.value.id === id) {
        currentEstimate.value = null
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete estimate'
      console.error('Error deleting estimate:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const searchEstimates = async (query) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/estimates/search?query=${encodeURIComponent(query)}`)
      estimates.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to search estimates'
      console.error('Error searching estimates:', err)
    } finally {
      loading.value = false
    }
  }

  const getEstimatesByStatus = async (status) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/estimates/status/${status}`)
      estimates.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch estimates by status'
      console.error('Error fetching estimates by status:', err)
    } finally {
      loading.value = false
    }
  }

  const getEstimatesByCustomer = async (customerId) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/estimates/customer/${customerId}`)
      estimates.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch estimates by customer'
      console.error('Error fetching estimates by customer:', err)
    } finally {
      loading.value = false
    }
  }

  const markAsSent = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`/api/estimates/${id}/mark-sent`)
      const updatedEstimate = response.data.data

      // Update in estimates array
      const index = estimates.value.findIndex(e => e.id === id)
      if (index !== -1) {
        estimates.value[index] = updatedEstimate
      }

      // Update current estimate if it's the same
      if (currentEstimate.value && currentEstimate.value.id === id) {
        currentEstimate.value = updatedEstimate
      }

      return updatedEstimate
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to mark estimate as sent'
      console.error('Error marking estimate as sent:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const markAsAccepted = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`/api/estimates/${id}/mark-accepted`)
      const updatedEstimate = response.data.data

      // Update in estimates array
      const index = estimates.value.findIndex(e => e.id === id)
      if (index !== -1) {
        estimates.value[index] = updatedEstimate
      }

      // Update current estimate if it's the same
      if (currentEstimate.value && currentEstimate.value.id === id) {
        currentEstimate.value = updatedEstimate
      }

      return updatedEstimate
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to mark estimate as accepted'
      console.error('Error marking estimate as accepted:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const markAsRejected = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`/api/estimates/${id}/mark-rejected`)
      const updatedEstimate = response.data.data

      // Update in estimates array
      const index = estimates.value.findIndex(e => e.id === id)
      if (index !== -1) {
        estimates.value[index] = updatedEstimate
      }

      // Update current estimate if it's the same
      if (currentEstimate.value && currentEstimate.value.id === id) {
        currentEstimate.value = updatedEstimate
      }

      return updatedEstimate
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to mark estimate as rejected'
      console.error('Error marking estimate as rejected:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const markAsExpired = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post(`/api/estimates/${id}/mark-expired`)
      const updatedEstimate = response.data.data

      // Update in estimates array
      const index = estimates.value.findIndex(e => e.id === id)
      if (index !== -1) {
        estimates.value[index] = updatedEstimate
      }

      // Update current estimate if it's the same
      if (currentEstimate.value && currentEstimate.value.id === id) {
        currentEstimate.value = updatedEstimate
      }

      return updatedEstimate
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to mark estimate as expired'
      console.error('Error marking estimate as expired:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const generatePdf = async (id) => {
    loading.value = true
    error.value = null

    try {
      const response = await axios.get(`/api/estimates/${id}/pdf`)
      return response.data.pdf_content
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to generate PDF'
      console.error('Error generating PDF:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  const clearCurrentEstimate = () => {
    currentEstimate.value = null
  }

  return {
    // State
    estimates,
    currentEstimate,
    loading,
    error,
    pagination,

    // Getters
    getEstimates,
    getCurrentEstimate,
    isLoading,
    getError,
    getPagination,

    // Actions
    fetchEstimates,
    fetchEstimate,
    createEstimate,
    updateEstimate,
    deleteEstimate,
    searchEstimates,
    getEstimatesByStatus,
    getEstimatesByCustomer,
    markAsSent,
    markAsAccepted,
    markAsRejected,
    markAsExpired,
    generatePdf,
    clearError,
    clearCurrentEstimate
  }
})
