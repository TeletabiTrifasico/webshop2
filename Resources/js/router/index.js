import { createRouter, createWebHistory } from 'vue-router'
import store from '../store'  // Changed from './store' to '../store'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/components/home/index.vue')
  },
  {
    path: '/products',
    name: 'products',
    component: () => import('@/components/products/index.vue')
  },
  {
    path: '/products/:id',
    name: 'product-show',
    component: () => import('@/components/products/Show.vue')
  },
  {
    path: '/cart',
    name: 'cart',
    component: () => import('@/components/cart/index.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/checkout',
    name: 'checkout',
    component: () => import('@/components/cart/Checkout.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/order-success/:orderId',
    name: 'order-success',
    component: () => import('@/components/orders/Success.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/auth/login',
    name: 'login',
    component: () => import('@/components/auth/Login.vue')
  },
  {
    path: '/auth/register',
    name: 'register',
    component: () => import('@/components/auth/Register.vue')
  },
  {
    path: '/admin',
    name: 'admin-dashboard',
    component: () => import('@/components/admin/Dashboard.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/products',
    name: 'admin-products',
    component: () => import('@/components/admin/products.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/products/create',
    name: 'admin-products-create',
    component: () => import('@/components/admin/ProductForm.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/products/edit/:id',
    name: 'admin-products-edit',
    component: () => import('@/components/admin/ProductForm.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/orders',
    name: 'admin-orders',
    component: () => import('@/components/admin/orders.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/orders/:id',
    name: 'admin-order-details',
    component: () => import('@/components/admin/order-details.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/users',
    name: 'admin-users',
    component: () => import('@/components/admin/users.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/users/create',
    name: 'admin-users-create',
    component: () => import('@/components/admin/UserForm.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/users/edit/:id',
    name: 'admin-users-edit',
    component: () => import('@/components/admin/UserForm.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/components/404.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }
    return { top: 0 }
  }
})

// Navigation guard for protected routes
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin)
  const isAuthenticated = !!store.state.auth.user
  const isAdmin = isAuthenticated && store.state.auth.user.role === 'admin'
  
  if (requiresAuth && !isAuthenticated) {
    next('/auth/login')
  } else if (requiresAdmin && !isAdmin) {
    next('/')
  } else {
    next()
  }
})

export default router