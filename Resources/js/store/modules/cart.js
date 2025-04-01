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
    async fetchCart({ commit, rootState }) {
      // Skip if user is not authenticated
      if (!rootState.auth.token) {
        commit('setItems', [])
        return
      }
      
      commit('setLoading', true)
      try {
        const response = await axios.get('/api/cart')
        if (response.data.success) {
          commit('setItems', response.data.items || [])
        } else {
          throw new Error(response.data.error || 'Failed to load cart')
        }
      } catch (error) {
        console.error('Error fetching cart:', error)
        commit('setError', 'Failed to load cart')
        commit('setItems', [])
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
        
        if (response.data.success) {
          // Update cart with returned items
          commit('setItems', response.data.items || [])
          return { success: true }
        } else {
          throw new Error(response.data.error || 'Failed to add item')
        }
      } catch (error) {
        console.error('Error adding to cart:', error)
        commit('setError', error.response?.data?.error || 'Failed to add item to cart')
        return { success: false, error: error.message }
      } finally {
        commit('setLoading', false)
      }
    },
    
    async updateItem({ commit }, { itemId, quantity }) {
      commit('setLoading', true)
      try {
        const response = await axios.post('/api/cart/update', { 
          item_id: itemId, 
          quantity 
        })
        
        if (response.data.success) {
          // Update local state immediately for responsive UI
          commit('updateItemQuantity', { id: itemId, quantity })
          // Update entire cart with server response
          commit('setItems', response.data.items || [])
          return { success: true }
        } else {
          throw new Error(response.data.error || 'Failed to update item')
        }
      } catch (error) {
        console.error('Error updating cart item:', error)
        commit('setError', 'Failed to update cart')
        return { success: false, error: error.message }
      } finally {
        commit('setLoading', false)
      }
    },
    
    async removeItem({ commit }, itemId) {
      commit('setLoading', true)
      try {
        const response = await axios.post('/api/cart/remove', { item_id: itemId })
        
        if (response.data.success) {
          // Update local state immediately
          commit('removeItem', itemId)
          // Update entire cart with server response
          commit('setItems', response.data.items || [])
          return { success: true }
        } else {
          throw new Error(response.data.error || 'Failed to remove item')
        }
      } catch (error) {
        console.error('Error removing cart item:', error)
        commit('setError', 'Failed to remove item')
        return { success: false, error: error.message }
      } finally {
        commit('setLoading', false)
      }
    },
    
    async clearCart({ commit }) {
      commit('setLoading', true)
      try {
        const response = await axios.post('/api/cart/clear')
        
        if (response.data.success) {
          commit('clearCart')
          return { success: true }
        } else {
          throw new Error(response.data.error || 'Failed to clear cart')
        }
      } catch (error) {
        console.error('Error clearing cart:', error)
        commit('setError', 'Failed to clear cart')
        return { success: false, error: error.message }
      } finally {
        commit('setLoading', false)
      }
    },
    
    async checkout({ commit }, checkoutData) {
      commit('setLoading', true)
      try {
        const response = await axios.post('/api/cart/checkout', checkoutData)
        
        if (response.data.success) {
          commit('clearCart')
          return { 
            success: true, 
            orderId: response.data.order_id 
          }
        } else {
          throw new Error(response.data.error || 'Failed to complete checkout')
        }
      } catch (error) {
        console.error('Checkout error:', error)
        commit('setError', 'Failed to complete checkout')
        return { success: false, error: error.response?.data?.error || error.message }
      } finally {
        commit('setLoading', false)
      }
    }
  }
}