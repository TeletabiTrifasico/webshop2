import axios from 'axios'
import store from '../store'
import router from '../router'

// Request interceptor to attach token
axios.interceptors.request.use(
    config => {
        const token = store.state.auth.token
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    error => {
        return Promise.reject(error)
    }
)

// Response interceptor for API calls
axios.interceptors.response.use(
    response => response,
    error => {
        // Log helpful debug information
        console.error(`API Error: ${error.config.url}`, error)

        // Handle 401 Unauthorized errors (expired or invalid token)
        if (error.response && error.response.status === 401) {
            // Check if we're already on the login page to avoid redirect loops
            if (router.currentRoute.value.path !== '/auth/login') {
                console.log('Unauthorized request, redirecting to login...')
                
                // Logout and redirect to login
                store.dispatch('auth/logout').then(() => {
                    router.push({ 
                        path: '/auth/login', 
                        query: { redirect: router.currentRoute.value.fullPath } 
                    })
                })
            }
        }
        
        return Promise.reject(error)
    }
)

export default axios