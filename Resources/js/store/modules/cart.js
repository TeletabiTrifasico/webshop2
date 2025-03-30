import axios from 'axios'

export default {
  namespaced: true,
  
  state: {
    items: [],
    count: 0
  },

  mutations: {
    setItems(state, items) {
      state.items = items
    },
    updateCount(state, count) {
      state.count = count
    }
  },

  actions: {
    async addItem({ commit }, { productId, quantity }) {
      try {
        const response = await axios.post('/cart/add', {
          product_id: productId,
          quantity
        })
        
        if (response.data.success) {
          commit('updateCount', response.data.cartCount)
          return { success: true }
        }
        return { success: false, error: response.data.message }
      } catch (error) {
        return { success: false, error: 'Failed to add item to cart' }
      }
    },

    async fetchCart({ commit }) {
      try {
        const response = await axios.get('/cart')
        commit('setItems', response.data.cart)
        commit('updateCount', response.data.cart.length)
      } catch (error) {
        console.error('Error fetching cart:', error)
      }
    }
  }
}