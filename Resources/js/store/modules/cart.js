import axios from 'axios'

export default {
  namespaced: true,
  
  state: {
    items: [],
    loading: false,
    error: null
  },

  mutations: {
    setItems(state, items) {
      state.items = items
    },
    setLoading(state, status) {
      state.loading = status
    },
    setError(state, error) {
      state.error = error
    },
    updateItemQuantity(state, { id, quantity }) {
      const item = state.items.find(item => item.id === id)
      if (item) {
        item.quantity = quantity
      }
    },
    removeItem(state, id) {
      state.items = state.items.filter(item => item.id !== id)
    },
    clearCart(state) {
      state.items = []
    }
  },

  actions: {
    async fetchCart({ commit }) {
      commit('setLoading', true)
      try {
        const response = await axios.get('/api/cart')
        commit('setItems', response.data.items || [])
      } catch (error) {
        commit('setError', 'Failed to load cart')
      } finally {
        commit('setLoading', false)
      }
    },
    
    async addItem({ commit, dispatch }, { productId, quantity = 1 }) {
      commit('setLoading', true)
      try {
        const response = await axios.post('/api/cart/add', { 
          product_id: productId, 
          quantity 
        })
        
        // Refresh cart after adding item
        await dispatch('fetchCart')
        return { success: true }
      } catch (error) {
        commit('setError', 'Failed to add item to cart')
        return { success: false, error: error.message }
      } finally {
        commit('setLoading', false)
      }
    },
    
    async updateItem({ commit, dispatch }, { itemId, quantity }) {
      commit('setLoading', true)
      try {
        await axios.post('/api/cart/update', { 
          item_id: itemId, 
          quantity 
        })
        
        // Update local state immediately for responsive UI
        commit('updateItemQuantity', { id: itemId, quantity })
        
        // Refresh cart to ensure consistency
        await dispatch('fetchCart')
        return { success: true }
      } catch (error) {
        commit('setError', 'Failed to update cart')
        return { success: false, error: error.message }
      } finally {
        commit('setLoading', false)
      }
    },
    
    async removeItem({ commit, dispatch }, itemId) {
      commit('setLoading', true)
      try {
        await axios.post('/api/cart/remove', { item_id: itemId })
        
        // Update local state immediately
        commit('removeItem', itemId)
        
        // Refresh cart to ensure consistency
        await dispatch('fetchCart')
        return { success: true }
      } catch (error) {
        commit('setError', 'Failed to remove item')
        return { success: false, error: error.message }
      } finally {
        commit('setLoading', false)
      }
    },
    
    async clearCart({ commit }) {
      commit('setLoading', true)
      try {
        await axios.post('/api/cart/clear')
        commit('clearCart')
        return { success: true }
      } catch (error) {
        commit('setError', 'Failed to clear cart')
        return { success: false, error: error.message }
      } finally {
        commit('setLoading', false)
      }
    },
    
    async checkout({ commit, dispatch }) {
      commit('setLoading', true)
      try {
        const response = await axios.post('/api/cart/checkout')
        commit('clearCart')
        return { 
          success: true, 
          orderId: response.data.order_id 
        }
      } catch (error) {
        commit('setError', 'Failed to complete checkout')
        return { success: false, error: error.message }
      } finally {
        commit('setLoading', false)
      }
    }
  }
}