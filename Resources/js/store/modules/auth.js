import axios from 'axios'

export default {
    namespaced: true,

    state: {
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('token') || null,
        loading: false,
        error: null
    },

    mutations: {
        setAuth(state, { user, token }) {
            state.user = user
            state.token = token
            
            // Store in localStorage for persistence
            if (user && token) {
                localStorage.setItem('user', JSON.stringify(user))
                localStorage.setItem('token', token)
            } else {
                localStorage.removeItem('user')
                localStorage.removeItem('token')
            }
        },
        
        setLoading(state, loading) {
            state.loading = loading
        },
        
        setError(state, error) {
            state.error = error
        }
    },

    actions: {
        async register({ commit }, userData) {
            commit('setLoading', true)
            commit('setError', null)
            
            try {
                const response = await axios.post('/api/auth/register', {
                    username: userData.username,
                    email: userData.email,
                    password: userData.password
                })
                
                if (response.data.success) {
                    // Save user and token
                    commit('setAuth', {
                        user: response.data.user,
                        token: response.data.token
                    })
                    
                    // Set Authorization header for future requests
                    axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`
                    
                    return { success: true }
                }
                
                throw new Error(response.data.error || 'Registration failed')
            } catch (error) {
                const message = error.response?.data?.error || error.message
                commit('setError', message)
                return { success: false, error: message }
            } finally {
                commit('setLoading', false)
            }
        },

        async login({ commit }, credentials) {
            commit('setLoading', true)
            commit('setError', null)
            
            try {
                const response = await axios.post('/api/auth/login', credentials)
                
                if (response.data.success) {
                    // Save user and token
                    commit('setAuth', {
                        user: response.data.user,
                        token: response.data.token
                    })
                    
                    // Set Authorization header for future requests
                    axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`
                    
                    return { success: true }
                }
                
                throw new Error(response.data.error || 'Login failed')
            } catch (error) {
                const message = error.response?.data?.error || error.message
                commit('setError', message)
                return { success: false, error: message }
            } finally {
                commit('setLoading', false)
            }
        },

        async logout({ commit }) {
            try {
                await axios.post('/api/auth/logout')
            } catch (error) {
                console.error('Logout error:', error)
            } finally {
                // Always clear auth state
                commit('setAuth', { user: null, token: null })
                
                // Remove Authorization header
                delete axios.defaults.headers.common['Authorization']
            }
            
            return { success: true }
        },
        
        // Initialize auth from localStorage
        initAuth({ commit, state }) {
            if (state.token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${state.token}`
            }
        }
    },
    
    getters: {
        isAuthenticated: state => !!state.token,
        isAdmin: state => state.user && state.user.role === 'admin',
        user: state => state.user
    }
}