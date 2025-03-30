<template>
  <div class="container mt-4">
    <div v-if="product" class="row">
      <div class="col-md-6">
        <img :src="product.image" 
             :alt="product.name" 
             class="img-fluid rounded">
      </div>
      <div class="col-md-6">
        <h1>{{ product.name }}</h1>
        <p class="lead">${{ formatPrice(product.price) }}</p>
        <p>{{ product.description }}</p>
        
        <div class="d-flex align-items-center mb-3">
          <label class="me-2">Quantity:</label>
          <input type="number" 
                 v-model.number="quantity" 
                 min="1" 
                 class="form-control w-25">
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

    <div v-else class="text-center">
      <p>Loading product details...</p>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'

export default {
  name: 'ProductDetail',

  data() {
    return {
      product: null,
      quantity: 1
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

    async addToCart() {
      if (!this.isAuthenticated) {
        this.$router.push('/auth/login')
        return
      }

      try {
        const result = await this.addItem({
          productId: this.product.id,
          quantity: this.quantity
        })

        if (result.success) {
          this.$toast.success('Product added to cart')
        } else {
          this.$toast.error(result.error || 'Failed to add product to cart')
        }
      } catch (error) {
        console.error('Error adding to cart:', error)
        this.$toast.error('Failed to add product to cart')
      }
    }
  },

  async created() {
    try {
      const response = await this.$axios.get(`/products/${this.$route.params.id}`)
      this.product = response.data.product
    } catch (error) {
      console.error('Error fetching product:', error)
      this.$toast.error('Failed to load product details')
    }
  }
}
</script>