import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAppointmentStore = defineStore('appointment', () => {
  const appointments = ref([])
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: null,
    to: null
  })

  const upcomingAppointments = ref([])

  // Getters
  const getAppointments = computed(() => appointments.value)
  const getUpcomingAppointments = computed(() => upcomingAppointments.value)
  const isLoading = computed(() => loading.value)
  const getError = computed(() => error.value)
  const getPagination = computed(() => pagination.value)

  // Actions
  const fetchAppointments = async (params = {}) => {
    loading.value = true
    error.value = null

    try {
      const token = localStorage.getItem('auth_token')
      const queryParams = new URLSearchParams({
        page: params.page || 1,
        per_page: params.per_page || 15,
        ...params
      })

      const response = await fetch(`/api/appointments?${queryParams}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      })

      if (response.ok) {
        const data = await response.json()
        appointments.value = data.data
        pagination.value = data.meta


      } else {
        console.error('API Error:', response.status, response.statusText)
        throw new Error('Failed to fetch appointments')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error fetching appointments:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchUpcomingAppointments = async (limit = 10) => {
    try {
      const token = localStorage.getItem('auth_token')
      const response = await fetch(`/api/appointments/upcoming?limit=${limit}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      })

      if (response.ok) {
        const data = await response.json()
        upcomingAppointments.value = data.data
      }
    } catch (err) {
      console.error('Error fetching upcoming appointments:', err)
    }
  }

  const fetchAppointment = async (id) => {
    loading.value = true
    error.value = null

    try {
      const token = localStorage.getItem('auth_token')
      const response = await fetch(`/api/appointments/${id}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      })

      if (response.ok) {
        const appointment = await response.json()
        return appointment
      } else {
        throw new Error('Failed to fetch appointment')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error fetching appointment:', err)
      return null
    } finally {
      loading.value = false
    }
  }

  const createAppointment = async (appointmentData) => {
    loading.value = true
    error.value = null

    try {
      const token = localStorage.getItem('auth_token')
      const response = await fetch('/api/appointments', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(appointmentData)
      })

      if (response.ok) {
        const data = await response.json()
        appointments.value.unshift(data.data)
        return data.data
      } else {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Failed to create appointment')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error creating appointment:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateAppointment = async (id, appointmentData) => {
    loading.value = true
    error.value = null

    try {
      const token = localStorage.getItem('auth_token')
      const response = await fetch(`/api/appointments/${id}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(appointmentData)
      })

      if (response.ok) {
        const data = await response.json()
        const index = appointments.value.findIndex(app => app.id === id)
        if (index !== -1) {
          appointments.value[index] = data.data
        }
        return data.data
      } else {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Failed to update appointment')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error updating appointment:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteAppointment = async (id) => {
    loading.value = true
    error.value = null

    try {
      const token = localStorage.getItem('auth_token')
      const response = await fetch(`/api/appointments/${id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      })

      if (response.ok) {
        appointments.value = appointments.value.filter(app => app.id !== id)
        return true
      } else {
        throw new Error('Failed to delete appointment')
      }
    } catch (err) {
      error.value = err.message
      console.error('Error deleting appointment:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const checkAvailability = async (startTime, endTime, excludeAppointmentId = null) => {
    try {
      const token = localStorage.getItem('auth_token')
      const response = await fetch('/api/appointments/check-availability', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          start_time: startTime,
          end_time: endTime,
          exclude_appointment_id: excludeAppointmentId
        })
      })

      if (response.ok) {
        const data = await response.json()
        return data.available
      }
      return false
    } catch (err) {
      console.error('Error checking availability:', err)
      return false
    }
  }

  const markAsConfirmed = async (id) => {
    return updateAppointmentStatus(id, 'confirm')
  }

  const markAsCompleted = async (id) => {
    return updateAppointmentStatus(id, 'complete')
  }

  const markAsCancelled = async (id) => {
    return updateAppointmentStatus(id, 'cancel')
  }

  const markAsNoShow = async (id) => {
    return updateAppointmentStatus(id, 'no-show')
  }

  const updateAppointmentStatus = async (id, status) => {
    try {
      const token = localStorage.getItem('auth_token')
      const response = await fetch(`/api/appointments/${id}/${status}`, {
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      })

      if (response.ok) {
        const data = await response.json()
        const index = appointments.value.findIndex(app => app.id === id)
        if (index !== -1) {
          appointments.value[index] = data.data
        }
        return data.data
      } else {
        throw new Error(`Failed to mark appointment as ${status}`)
      }
    } catch (err) {
      error.value = err.message
      console.error(`Error marking appointment as ${status}:`, err)
      throw err
    }
  }

  const clearError = () => {
    error.value = null
  }

  const reset = () => {
    appointments.value = []
    upcomingAppointments.value = []
    loading.value = false
    error.value = null
    pagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
      from: null,
      to: null
    }
  }

  return {
    // State
    appointments,
    upcomingAppointments,
    loading,
    error,
    pagination,

    // Getters
    getAppointments,
    getUpcomingAppointments,
    isLoading,
    getError,
    getPagination,

    // Actions
    fetchAppointments,
    fetchUpcomingAppointments,
    fetchAppointment,
    createAppointment,
    updateAppointment,
    deleteAppointment,
    checkAvailability,
    markAsConfirmed,
    markAsCompleted,
    markAsCancelled,
    markAsNoShow,
    clearError,
    reset
  }
})
