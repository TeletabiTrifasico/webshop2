import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import axios from 'axios'

const app = createApp(App)

// Enable devtools in development
if (process.env.NODE_ENV === 'development') {
    app.config.devtools = true
}

// Configure axios
axios.defaults.baseURL = '' // Remove any baseURL to use relative paths
app.config.globalProperties.$axios = axios

app.use(router)
app.use(store)
app.mount('#app')