import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '@/views/Dashboard.vue';
import CustomerList from '@/views/Customers/CustomerList.vue';
import CustomerCreate from '@/views/Customers/CustomerCreate.vue';
import CustomerEdit from '@/views/Customers/CustomerEdit.vue';
import CustomerShow from '@/views/Customers/CustomerShow.vue';
import JobList from '@/views/Jobs/JobList.vue';
import JobCreate from '@/views/Jobs/JobCreate.vue';
import JobEdit from '@/views/Jobs/JobEdit.vue';
import JobShow from '@/views/Jobs/JobShow.vue';

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/Auth/Login.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/customers',
    name: 'customers.index',
    component: CustomerList,
    meta: { requiresAuth: true }
  },
  {
    path: '/customers/create',
    name: 'customers.create',
    component: CustomerCreate,
    meta: { requiresAuth: true }
  },
  {
    path: '/customers/:id',
    name: 'customers.show',
    component: CustomerShow,
    meta: { requiresAuth: true }
  },
  {
    path: '/customers/:id/edit',
    name: 'customers.edit',
    component: CustomerEdit,
    meta: { requiresAuth: true }
  },
  {
    path: '/jobs',
    name: 'jobs.index',
    component: JobList,
    meta: { requiresAuth: true }
  },
  {
    path: '/jobs/create',
    name: 'jobs.create',
    component: JobCreate,
    meta: { requiresAuth: true }
  },
  {
    path: '/jobs/:id',
    name: 'jobs.show',
    component: JobShow,
    meta: { requiresAuth: true }
  },
  {
    path: '/jobs/:id/edit',
    name: 'jobs.edit',
    component: JobEdit,
    meta: { requiresAuth: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation guard for authentication
router.beforeEach(async (to, from, next) => {
  // Import the auth store dynamically to avoid circular dependencies
  const { useAuthStore } = await import('@/stores/authStore');
  const authStore = useAuthStore();

  // Only initialize auth if we haven't tried yet and we're not going to login
  if (!authStore.getIsAuthenticated && authStore.getUser === null && to.path !== '/login') {
    try {
      await authStore.initializeAuth();
    } catch (error) {
      // If initialization fails, user is not authenticated
      console.log('Auth initialization failed, user not authenticated');
    }
  }

  if (to.meta.requiresAuth && !authStore.getIsAuthenticated) {
    next('/login');
  } else if (to.meta.requiresGuest && authStore.getIsAuthenticated) {
    next('/');
  } else {
    next();
  }
});

export default router;
