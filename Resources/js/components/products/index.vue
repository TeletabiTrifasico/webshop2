<template>
  <div class="container mt-4">
    <h1 class="mb-4">All Products</h1>

    <div v-if="loading" class="text-center">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <div v-else-if="products.length === 0" class="alert alert-info">
      No products available.
    </div>

    <div v-else class="row">
      <div v-for="product in products" 
           :key="product.id" 
           class="col-md-3 mb-4">
        <div class="card h-100">
          <img v-if="product.image" 
               :src="product.image" 
               class="card-img-top" 
               :alt="product.name"
               style="height: 200px; object-fit: cover;">
          <div class="card-body">
            <h5 class="card-title">{{ product.name }}</h5>
            <p class="card-text">${{ formatPrice(product.price) }}</p>
          </div>
          <div class="card-footer bg-white border-top-0">
            <router-link :to="`/products/${product.id}`" 
                        class="btn btn-primary w-100">
              View Details
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProductIndex',

  data() {
    return {
      products: [],
      loading: true,
      error: null
    }
  },

  methods: {
    formatPrice(price) {
      return Number(price).toFixed(2)
    },

    async fetchProducts() {
      try {
        console.log('Fetching products...');
        const response = await this.$axios.get('/api/products');
        console.log('Products response:', response.data);
        this.products = response.data.products;
      } catch (error) {
        console.error('Error fetching products:', error);
        this.error = 'Failed to load products';
      } finally {
        this.loading = false;
      }
    }
  },

  created() {
    this.fetchProducts();
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