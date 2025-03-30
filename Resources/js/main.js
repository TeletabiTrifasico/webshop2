import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import axios from 'axios'

// Configure axios defaults
axios.defaults.baseURL = window.location.origin
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const app = createApp(App)

// Make axios available in components
app.config.globalProperties.$axios = axios

app.use(router)
app.use(store)

app.mount('#app')