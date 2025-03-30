<template>
  <div class="container mt-4">
    <div v-if="loading" class="text-center">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <div v-else class="row">
      <div class="col-md-6">
        <img :src="product.image" 
             :alt="product.name" 
             class="img-fluid rounded">
      </div>
      <div class="col-md-6">
        <h1>{{ product.name }}</h1>
        <p class="lead mb-4">${{ formatPrice(product.price) }}</p>
        <p class="mb-4">{{ product.description }}</p>
        
        <div class="d-flex align-items-center mb-4">
          <div class="input-group" style="width: 150px;">
            <button class="btn btn-outline-secondary" 
                    @click="quantity > 1 && quantity--">-</button>
            <input type="number" 
                   class="form-control text-center" 
                   v-model.number="quantity" 
                   min="1">
            <button class="btn btn-outline-secondary" 
                    @click="quantity++">+</button>
          </div>
        </div>

        <button class="btn btn-primary btn-lg"
                @click="addToCart"
                :disabled="!isAuthenticated">
          Add to Cart
        </button>

        <div v-if="!isAuthenticated" class="alert alert-warning mt-3">
          Please <router-link to="/auth/login">login</router-link> to add items to cart
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'

export default {
  name: 'ProductShow',

  data() {
    return {
      product: null,
      quantity: 1,
      loading: true,
      error: null
    }
  },

  computed: {
    ...mapState('auth', {
      isAuthenticated: state => !!state.user
    })
  },

  methods: {
    ...mapActions('cart', ['addItem']),

    formatPrice(price) {
      return Number(price).toFixed(2)
    },

    async fetchProduct() {
      try {
        const response = await this.$axios.get(`/api/products/${this.$route.params.id}`)
        this.product = response.data.product
      } catch (error) {
        console.error('Error fetching product:', error)
        this.error = 'Failed to load product'
      } finally {
        this.loading = false
      }
    },

    async addToCart() {
      if (!this.isAuthenticated) {
        this.$router.push('/auth/login')
        return
      }

      try {
        await this.addItem({
          productId: this.product.id,
          quantity: this.quantity
        })
        // Add toast notification here if you have a notification system
        alert('Product added to cart')
      } catch (error) {
        console.error('Error adding to cart:', error)
        alert('Failed to add product to cart')
      }
    }
  },

  created() {
    this.fetchProduct()
  }
}
</script>