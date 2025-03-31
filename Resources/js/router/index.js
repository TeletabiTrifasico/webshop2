import { createRouter, createWebHistory } from 'vue-router'

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

export default router