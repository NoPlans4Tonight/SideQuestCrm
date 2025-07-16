import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '@/views/Dashboard.vue';
import CustomerList from '@/views/Customers/CustomerList.vue';
import CustomerCreate from '@/views/Customers/CustomerCreate.vue';
import CustomerEdit from '@/views/Customers/CustomerEdit.vue';
import CustomerShow from '@/views/Customers/CustomerShow.vue';

const routes = [
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
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation guard for authentication
router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth && !isAuthenticated()) {
    next('/login');
  } else {
    next();
  }
});

function isAuthenticated() {
  // Check if user is authenticated (implement based on your auth strategy)
  return localStorage.getItem('auth_token') !== null;
}

export default router;
