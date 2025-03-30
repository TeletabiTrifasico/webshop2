<template>
  <div class="container mt-4">
    <h1 class="mb-4">Our Products</h1>

    <div class="row">
      <template v-if="products && products.length">
        <div v-for="product in products" 
             :key="product.id" 
             class="col-md-4 mb-4">
          <div class="card h-100">
            <img :src="product.image" 
                 :alt="product.name"
                 class="card-img-top">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">{{ product.name }}</h5>
              <p class="card-text flex-grow-1">{{ product.description }}</p>
              <div class="mt-auto">
                <p class="card-text">
                  <strong>${{ formatPrice(product.price) }}</strong>
                </p>
                <div class="d-flex justify-content-between align-items-center">
                  <router-link :to="`/products/${product.id}`" 
                             class="btn btn-primary">
                    View Details
                  </router-link>
                  <button class="btn btn-success" 
                          @click="addToCart(product.id)"
                          :disabled="!isAuthenticated">
                    Add to Cart
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
      <div v-else class="col-12">
        <p class="text-center">No products available.</p>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'

export default {
  name: 'ProductIndex',

  data() {
    return {
      products: []
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

    async addToCart(productId) {
      if (!this.isAuthenticated) {
        this.$router.push('/auth/login')
        return
      }

      try {
        const result = await this.addItem({ productId, quantity: 1 })
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
      const response = await this.$axios.get('/products')
      this.products = response.data.products
    } catch (error) {
      console.error('Error fetching products:', error)
      this.$toast.error('Failed to load products')
    }
  }
}
</script>

<style scoped>
.card-img-top {
  height: 200px;
  object-fit: cover;
}

.card {
  transition: transform 0.2s;
}

.card:hover {
  transform: translateY(-5px);
}
</style>