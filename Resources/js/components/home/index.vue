<template>
  <div class="container mt-4">
    <div class="jumbotron text-center">
      <h1 class="display-4">Welcome to Our Webshop</h1>
      <p class="lead">Discover our amazing products at great prices!</p>
      <router-link to="/products" class="btn btn-primary btn-lg">
        Shop Now
      </router-link>
    </div>

    <div class="row mt-5">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center">
            <i class="fas fa-truck fa-3x mb-3"></i>
            <h5>Free Shipping</h5>
            <p>On orders over $50</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center">
            <i class="fas fa-undo fa-3x mb-3"></i>
            <h5>Easy Returns</h5>
            <p>30-day return policy</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center">
            <i class="fas fa-lock fa-3x mb-3"></i>
            <h5>Secure Payment</h5>
            <p>100% secure checkout</p>
          </div>
        </div>
      </div>
    </div>

    <div class="featured-products mt-5">
      <h2 class="text-center mb-4">Featured Products</h2>
      <div class="row">
        <div v-for="product in featuredProducts" 
             :key="product.id" 
             class="col-md-3">
          <div class="card">
            <img :src="product.image" 
                 :alt="product.name" 
                 class="card-img-top">
            <div class="card-body">
              <h5 class="card-title">{{ product.name }}</h5>
              <p class="card-text">${{ formatPrice(product.price) }}</p>
              <router-link :to="`/products/${product.id}`" 
                          class="btn btn-primary">
                View Details
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Home',
  
  data() {
    return {
      featuredProducts: []
    }
  },

  methods: {
    formatPrice(price) {
      return Number(price).toFixed(2)
    },

    async fetchFeaturedProducts() {
      try {
        const response = await this.$axios.get('/products/featured')
        this.featuredProducts = response.data.products
      } catch (error) {
        console.error('Error fetching featured products:', error)
      }
    }
  },

  created() {
    this.fetchFeaturedProducts()
  }
}
</script>