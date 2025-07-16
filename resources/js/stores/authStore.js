import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null);
  const isAuthenticated = ref(false);
  const isLoading = ref(false);
  const error = ref(null);

  // Getters
  const getUser = computed(() => user.value);
  const getIsAuthenticated = computed(() => isAuthenticated.value);
  const getIsLoading = computed(() => isLoading.value);
  const getError = computed(() => error.value);

  // Actions
  const initializeAuth = async () => {
    try {
      // Check if user is already authenticated via API
      const response = await axios.get('/api/user');
      user.value = response.data.user;
      isAuthenticated.value = true;
      error.value = null;
    } catch (err) {
      // User is not authenticated, clear state
      user.value = null;
      isAuthenticated.value = false;
      error.value = null;
    }
  };

  const login = async (credentials) => {
    isLoading.value = true;
    error.value = null;

    try {
      // Step 1: Get CSRF cookie (required for Sanctum SPA auth)
      await axios.get('/sanctum/csrf-cookie');

      // Step 2: Login with credentials via session/cookie
      await axios.post('/login', credentials, {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        }
      });

      // Step 3: Get user data
      const userResponse = await axios.get('/api/user');
      user.value = userResponse.data.user;
      isAuthenticated.value = true;

      return user.value;
    } catch (err) {
      console.error('Login error:', err.response?.data || err.message);
      error.value = err.response?.data?.message || 'Login failed';
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  const logout = async () => {
    try {
      await axios.post('/logout');
    } catch (err) {
      console.error('Logout error:', err);
    } finally {
      // Clear local state regardless of API call success
      user.value = null;
      isAuthenticated.value = false;
      error.value = null;
    }
  };

  const clearError = () => {
    error.value = null;
  };

  return {
    // State
    user,
    isAuthenticated,
    isLoading,
    error,

    // Getters
    getUser,
    getIsAuthenticated,
    getIsLoading,
    getError,

    // Actions
    initializeAuth,
    login,
    logout,
    clearError,
  };
});
