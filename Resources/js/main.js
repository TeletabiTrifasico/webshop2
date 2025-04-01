import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import axios from 'axios'
import './services/axios-interceptor' // Import axios interceptors

// Configure axios defaults
axios.defaults.baseURL = window.location.origin
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Initialize authentication from localStorage
store.dispatch('auth/initAuth').then(() => {
  // After auth is initialized, fetch the cart
  if (store.getters['auth/isAuthenticated']) {
    store.dispatch('cart/fetchCart')
  }
})

const app = createApp(App)

// Make axios available in components
app.config.globalProperties.$axios = axios

app.use(router)
app.use(store)

app.mount('#app')