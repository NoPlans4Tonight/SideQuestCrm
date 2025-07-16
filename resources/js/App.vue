<template>
  <div id="app" class="min-h-screen bg-gray-100">
    <!-- Navigation - Only show when authenticated -->
    <nav v-if="authStore.isAuthenticated" class="bg-white border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <!-- Logo -->
            <div class="shrink-0 flex items-center">
              <router-link to="/" class="text-xl font-bold text-gray-900">
                SideQuest CRM
              </router-link>
            </div>

            <!-- Navigation Links -->
            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
              <router-link
                to="/"
                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                :class="$route.name === 'dashboard' ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
              >
                Dashboard
              </router-link>
              <router-link
                to="/customers"
                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                :class="$route.name?.startsWith('customers') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
              >
                Customers
              </router-link>
              <router-link
                to="/jobs"
                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                :class="$route.name?.startsWith('jobs') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
              >
                Jobs
              </router-link>
            </div>
          </div>

          <div class="hidden sm:flex sm:items-center sm:ms-6">
            <!-- Settings Dropdown -->
            <div class="ms-3 relative">
              <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-700">{{ authStore.user?.name || 'User' }}</span>
                <button
                  @click="logout"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150"
                >
                  Logout
                </button>
              </div>
            </div>
          </div>

          <!-- Hamburger -->
          <div class="-me-2 flex items-center sm:hidden">
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
            >
              <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': mobileMenuOpen, 'inline-flex': !mobileMenuOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': !mobileMenuOpen, 'inline-flex': mobileMenuOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Responsive Navigation Menu -->
      <div :class="{'block': mobileMenuOpen, 'hidden': !mobileMenuOpen}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <router-link
            to="/"
            class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium leading-6 transition duration-150 ease-in-out"
            :class="$route.name === 'dashboard' ? 'bg-indigo-50 border-indigo-400 text-indigo-700' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'"
          >
            Dashboard
          </router-link>
          <router-link
            to="/customers"
            class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium leading-6 transition duration-150 ease-in-out"
            :class="$route.name?.startsWith('customers') ? 'bg-indigo-50 border-indigo-400 text-indigo-700' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'"
          >
            Customers
          </router-link>
          <router-link
            to="/jobs"
            class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium leading-6 transition duration-150 ease-in-out"
            :class="$route.name?.startsWith('jobs') ? 'bg-indigo-50 border-indigo-400 text-indigo-700' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'"
          >
            Jobs
          </router-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
          <div class="flex items-center px-4">
            <div>
              <div class="font-medium text-base text-gray-800">{{ authStore.user?.name || 'User' }}</div>
              <div class="font-medium text-sm text-gray-500">{{ authStore.user?.email || 'user@example.com' }}</div>
            </div>
          </div>

          <div class="mt-3 space-y-1">
            <button
              @click="logout"
              class="block w-full text-left px-4 py-2 text-base font-medium leading-6 text-gray-700 hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition duration-150 ease-in-out"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main>
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const mobileMenuOpen = ref(false)

onMounted(() => {
  authStore.initialize()
})

const logout = async () => {
  try {
    // Call logout API endpoint
    await fetch('/api/logout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      }
    })
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    authStore.logout()
    router.push('/login')
  }
}
</script>

<style>
/* Global styles */
</style>
