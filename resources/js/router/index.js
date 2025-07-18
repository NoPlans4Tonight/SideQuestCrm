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
import ServiceList from '@/views/Services/ServiceList.vue';
import ServiceCreate from '@/views/Services/ServiceCreate.vue';
import ServiceEdit from '@/views/Services/ServiceEdit.vue';
import AppointmentList from '@/views/Appointments/AppointmentList.vue';
import AppointmentCreate from '@/views/Appointments/AppointmentCreate.vue';
import EstimateList from '@/views/Estimates/EstimateList.vue';
import EstimateCreate from '@/views/Estimates/EstimateCreate.vue';

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
  },
  {
    path: '/services',
    name: 'services.index',
    component: ServiceList,
    meta: { requiresAuth: true }
  },
  {
    path: '/services/create',
    name: 'services.create',
    component: ServiceCreate,
    meta: { requiresAuth: true }
  },
  {
    path: '/services/:id/edit',
    name: 'services.edit',
    component: ServiceEdit,
    meta: { requiresAuth: true }
  },
  {
    path: '/appointments',
    name: 'appointments.index',
    component: AppointmentList,
    meta: { requiresAuth: true }
  },
  {
    path: '/appointments/create',
    name: 'appointments.create',
    component: AppointmentCreate,
    meta: { requiresAuth: true }
  },
  {
    path: '/estimates',
    name: 'estimates.index',
    component: EstimateList,
    meta: { requiresAuth: true }
  },
  {
    path: '/estimates/create',
    name: 'estimates.create',
    component: EstimateCreate,
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

  // Always initialize auth if we haven't tried yet
  if (authStore.getUser === null) {
    try {
      await authStore.initializeAuth();
    } catch (error) {
      // If initialization fails, user is not authenticated
      console.log('Auth initialization failed, user not authenticated');
    }
  }

  // Redirect authenticated users away from guest-only routes
  if (to.meta.requiresGuest && authStore.getIsAuthenticated) {
    next('/');
    return;
  }

  // Redirect unauthenticated users away from protected routes
  if (to.meta.requiresAuth && !authStore.getIsAuthenticated) {
    next('/login');
    return;
  }

  // Allow navigation
  next();
});

export default router;
