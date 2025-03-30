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
    path: '/auth/login',
    name: 'login',
    component: () => import('@/components/auth/Login.vue')
  },
  {
    path: '/auth/register',
    name: 'register',
    component: () => import('@/components/auth/Register.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router