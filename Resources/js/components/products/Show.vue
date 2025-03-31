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
        <div class="product-image-container">
          <img :src="product.image" 
               :alt="product.name" 
               class="img-fluid rounded product-detail-image">
        </div>
      </div>
      <div class="col-md-6">
        <h1>{{ product.name }}</h1>
        <p class="lead price mb-4">${{ formatPrice(product.price) }}</p>
        <p class="mb-4 description">{{ product.description }}</p>
        
        <div class="d-flex align-items-center mb-4">
          <div class="quantity-control">
            <button class="btn btn-outline-secondary" 
                    @click="quantity > 1 && quantity--">-</button>
            <input 
                   class="form-control text-center" 
                   v-model.number="quantity" 
                   min="1">
            <button class="btn btn-outline-secondary" 
                    @click="quantity++">+</button>
          </div>
        </div>

        <button class="btn btn-primary btn-lg w-100"
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

<style scoped>
.product-image-container {
  height: 400px;
  width: 100%;
  overflow: hidden;
  background-color: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.product-detail-image {
  max-height: 100%;
  max-width: 100%;
  object-fit: contain;
}

.price {
  font-size: 2rem;
  color: #00b300;
  font-weight: bold;
}

.description {
  font-size: 1.1rem;
  line-height: 1.6;
  color: #6c757d;
}

.quantity-control {
  display: flex;
  align-items: center;
  width: 150px;
}

.quantity-control input {
  width: 60px;
  text-align: center;
}

.quantity-control button {
  width: 40px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 768px) {
  .product-image-container {
    height: 300px;
    margin-bottom: 1rem;
  }
  
  .price {
    font-size: 1.5rem;
    margin-bottom: 1rem;
  }
}
</style>