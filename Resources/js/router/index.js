import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/components/home/Index.vue')
  },
  {
    path: '/products',
    name: 'products',
    component: () => import('@/components/products/Index.vue')
  },
  {
    path: '/products/:id',
    name: 'product-detail',
    component: () => import('@/components/products/Detail.vue')
  },
  {
    path: '/cart',
    name: 'cart',
    component: () => import('@/components/cart/Index.vue')
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
    path: '/user/profile',
    name: 'profile',
    component: () => import('@/components/user/Profile.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/components/404.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('user')
  
  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'login' })
  } else {
    next()
  }
})

export default router