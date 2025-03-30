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
    name: 'product-details',
    component: () => import('@/components/products/Show.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router