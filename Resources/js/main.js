import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'

const app = createApp(App)

// Enable devtools in development
if (process.env.NODE_ENV === 'development') {
    app.config.devtools = true
}

app.use(router)
app.use(store)

app.mount('#app')