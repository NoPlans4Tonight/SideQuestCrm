import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useServiceStore = defineStore('service', () => {
  // State
  const services = ref([]);
  const currentService = ref(null);
  const loading = ref(false);
  const error = ref(null);
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: null,
    to: null
  });

  // Getters
  const getServices = computed(() => services.value);
  const getCurrentService = computed(() => currentService.value);
  const isLoading = computed(() => loading.value);
  const getError = computed(() => error.value);
  const getPagination = computed(() => pagination.value);

  // Actions
  const fetchServices = async (page = 1, perPage = 15) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get('/api/services', {
        params: { page, per_page: perPage }
      });
      services.value = response.data.data;
      pagination.value = response.data.meta;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch services';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const fetchService = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`/api/services/${id}`);
      currentService.value = response.data.data;
      return response.data.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch service';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const createService = async (serviceData) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post('/api/services', serviceData);
      const newService = response.data.data;
      services.value.unshift(newService);
      return newService;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create service';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const updateService = async (id, serviceData) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`/api/services/${id}`, serviceData);
      const updatedService = response.data.data;
      const index = services.value.findIndex(s => s.id === id);
      if (index !== -1) {
        services.value[index] = updatedService;
      }
      if (currentService.value?.id === id) {
        currentService.value = updatedService;
      }
      return updatedService;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update service';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const deleteService = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      await axios.delete(`/api/services/${id}`);
      services.value = services.value.filter(s => s.id !== id);
      if (currentService.value?.id === id) {
        currentService.value = null;
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete service';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const clearError = () => {
    error.value = null;
  };

  const clearCurrentService = () => {
    currentService.value = null;
  };

  return {
    services,
    currentService,
    loading,
    error,
    pagination,
    getServices,
    getCurrentService,
    isLoading,
    getError,
    getPagination,
    fetchServices,
    fetchService,
    createService,
    updateService,
    deleteService,
    clearError,
    clearCurrentService
  };
});
