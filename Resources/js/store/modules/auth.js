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
        const response = await axios.post('/auth/login', credentials)
        commit('setUser', response.data.user)
        return { success: true }
      } catch (error) {
        commit('setError', error.response?.data?.message || 'Login failed')
        return { success: false, error: error.response?.data?.message }
      } finally {
        commit('setLoading', false)
      }
    },

    async logout({ commit }) {
      try {
        await axios.post('/auth/logout')
      } finally {
        commit('setUser', null)
      }
    }
  }
}