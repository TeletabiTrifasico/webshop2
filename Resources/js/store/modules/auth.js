import axios from 'axios'

export default {
  namespaced: true,

  state: {
    user: JSON.parse(localStorage.getItem('user')) || null,
    loading: false,
    error: null
  },

  mutations: {
    setUser(state, user) {
      state.user = user
      if (user) {
        localStorage.setItem('user', JSON.stringify(user))
      } else {
        localStorage.removeItem('user')
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
    async login({ commit }, credentials) {
      commit('setLoading', true)
      commit('setError', null)
      
      try {
        const response = await axios.post('/api/auth/login', credentials)
        if (response.data.success) {
          commit('setUser', response.data.user)
          return { success: true }
        } else {
          throw new Error(response.data.error)
        }
      } catch (error) {
        const errorMessage = error.response?.data?.error || 'Login failed'
        commit('setError', errorMessage)
        return { success: false, error: errorMessage }
      } finally {
        commit('setLoading', false)
      }
    },

    async register({ commit }, userData) {
      commit('setLoading', true)
      commit('setError', null)
      
      try {
        const response = await axios.post('/api/auth/register', userData)
        if (response.data.success) {
          commit('setUser', response.data.user)
          return { success: true }
        } else {
          throw new Error(response.data.error)
        }
      } catch (error) {
        const errorMessage = error.response?.data?.error || 'Registration failed'
        commit('setError', errorMessage)
        return { success: false, error: errorMessage }
      } finally {
        commit('setLoading', false)
      }
    },

    async logout({ commit }) {
      try {
        await axios.post('/api/auth/logout')
        commit('setUser', null)
        return { success: true }
      } catch (error) {
        console.error('Logout error:', error)
        return { success: false }
      }
    }
  }
}