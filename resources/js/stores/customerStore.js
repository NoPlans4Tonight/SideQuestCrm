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

      // Handle both enriched and non-enriched data structures
      if (response.data.data && response.data.data.length > 0 && response.data.data[0].customer) {
        // Enriched data structure - extract customer objects
        customers.value = response.data.data.map(item => item.customer);
      } else {
        // Non-enriched data structure - use data directly
        customers.value = response.data.data;
      }

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

      // Handle both enriched and non-enriched data structures
      if (response.data.data && response.data.data.customer) {
        // Enriched data structure - extract customer object
        currentCustomer.value = response.data.data.customer;
        return response.data.data;
      } else {
        // Non-enriched data structure - use data directly
        currentCustomer.value = response.data.data;
        return response.data.data;
      }
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

      // Add to the beginning of the list and update pagination
      customers.value.unshift(newCustomer);

      // Update pagination to reflect the new customer
      if (pagination.value.total !== undefined) {
        pagination.value.total += 1;
        pagination.value.to = Math.min((pagination.value.to || 0) + 1, pagination.value.per_page);

        // If we're at the per_page limit, remove the last item to maintain page size
        if (customers.value.length > pagination.value.per_page) {
          customers.value.pop();
        }
      }

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

      // Update in customers array - ensure proper ID comparison
      const customerId = parseInt(id);
      const index = customers.value.findIndex(c => parseInt(c.id) === customerId);
      if (index !== -1) {
        // Use Vue 3 reactive replacement to ensure reactivity
        customers.value.splice(index, 1, updatedCustomer);
      }

      // Update current customer if it's the same
      if (currentCustomer.value?.id && parseInt(currentCustomer.value.id) === customerId) {
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

      // Remove from customers array - ensure proper ID comparison
      const customerId = parseInt(id);
      customers.value = customers.value.filter(c => parseInt(c.id) !== customerId);

      // Clear current customer if it's the same
      if (currentCustomer.value?.id && parseInt(currentCustomer.value.id) === customerId) {
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
      // Reset to full list when search is cleared
      await fetchCustomers();
      return customers.value;
    }

    loading.value = true;
    error.value = null;

    try {
      const response = await axios.get('/api/customers/search', {
        params: { q: query }
      });

      // Update customers array with search results
      customers.value = response.data.data;

      // Reset pagination for search results
      pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: response.data.data.length,
        total: response.data.data.length,
        from: response.data.data.length > 0 ? 1 : null,
        to: response.data.data.length
      };

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

  // Force refresh customer data (useful after updates)
  const refreshCustomers = async (page = null) => {
    const currentPage = page || pagination.value.current_page;
    await fetchCustomers(currentPage);
  };

  // Helper method to get enriched customer data
  const getEnrichedCustomerData = () => {
    return currentCustomer.value;
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
    refreshCustomers,
    clearError,
    clearCurrentCustomer,
    getEnrichedCustomerData
  };
});
