import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useCustomerStore = defineStore('customer', () => {
  // State - single responsibility: manage customer data
  const customers = ref([]);
  const currentCustomer = ref(null);
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

  // Getters - computed properties
  const getCustomers = computed(() => customers.value);
  const getCurrentCustomer = computed(() => currentCustomer.value);
  const isLoading = computed(() => loading.value);
  const getError = computed(() => error.value);
  const getPagination = computed(() => pagination.value);

  // Actions - business logic
  const fetchCustomers = async (page = 1, perPage = 15) => {
    loading.value = true;
    error.value = null;

    try {
      const response = await axios.get('/api/customers', {
        params: { page, per_page: perPage }
      });

      customers.value = response.data.data;
      pagination.value = response.data.meta;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch customers';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const fetchCustomer = async (id) => {
    loading.value = true;
    error.value = null;

    try {
      const response = await axios.get(`/api/customers/${id}`);
      currentCustomer.value = response.data.data;
      return response.data.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch customer';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const createCustomer = async (customerData) => {
    loading.value = true;
    error.value = null;

    try {
      const response = await axios.post('/api/customers', customerData);
      const newCustomer = response.data.data;

      customers.value.unshift(newCustomer);
      return newCustomer;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create customer';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const updateCustomer = async (id, customerData) => {
    loading.value = true;
    error.value = null;

    try {
      const response = await axios.put(`/api/customers/${id}`, customerData);
      const updatedCustomer = response.data.data;

      // Update in customers array
      const index = customers.value.findIndex(c => c.id === id);
      if (index !== -1) {
        customers.value[index] = updatedCustomer;
      }

      // Update current customer if it's the same
      if (currentCustomer.value?.id === id) {
        currentCustomer.value = updatedCustomer;
      }

      return updatedCustomer;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update customer';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const deleteCustomer = async (id) => {
    loading.value = true;
    error.value = null;

    try {
      await axios.delete(`/api/customers/${id}`);

      // Remove from customers array
      customers.value = customers.value.filter(c => c.id !== id);

      // Clear current customer if it's the same
      if (currentCustomer.value?.id === id) {
        currentCustomer.value = null;
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete customer';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const searchCustomers = async (query) => {
    if (!query.trim()) {
      return [];
    }

    loading.value = true;
    error.value = null;

    try {
      const response = await axios.get('/api/customers/search', {
        params: { q: query }
      });

      return response.data.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to search customers';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const clearError = () => {
    error.value = null;
  };

  const clearCurrentCustomer = () => {
    currentCustomer.value = null;
  };

  return {
    // State
    customers,
    currentCustomer,
    loading,
    error,
    pagination,

    // Getters
    getCustomers,
    getCurrentCustomer,
    isLoading,
    getError,
    getPagination,

    // Actions
    fetchCustomers,
    fetchCustomer,
    createCustomer,
    updateCustomer,
    deleteCustomer,
    searchCustomers,
    clearError,
    clearCurrentCustomer
  };
});
