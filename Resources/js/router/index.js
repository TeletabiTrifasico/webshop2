import { createRouter, createWebHistory } from 'vue-router'
import store from '../store'

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
  const isAuthenticated = !!store.state.auth.user
  
  if (requiresAuth && !isAuthenticated) {
    next('/auth/login')
  } else {
    next()
  }
})

export default router