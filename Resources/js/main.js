import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import axios from 'axios'
import Header from '@/components/layouts/Header.vue'
import Footer from '@/components/layouts/Footer.vue'

const app = createApp(App)

// Configure axios
axios.defaults.baseURL = '/api'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
app.config.globalProperties.$axios = axios

// Register global components
app.component('app-header', Header)
app.component('app-footer', Footer)

// Use Vue Router and Vuex
app.use(router)
app.use(store)

app.mount('#app')