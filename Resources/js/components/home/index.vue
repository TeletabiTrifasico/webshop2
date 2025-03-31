<template>
  <div class="container mt-4">
    <div class="jumbotron text-center mb-5 bg-light p-5 rounded">
      <h1 class="display-4">Welcome to the Webshop</h1>
      <p class="lead">Discover our exclusive collection of items</p>
      <router-link to="/products" class="btn btn-primary btn-lg">
        View All Products
      </router-link>
    </div>

    <section class="latest-products">
      <h2 class="text-center mb-4">Latest Arrivals</h2>
      
      <div v-if="loading" class="text-center">
        <div class="spinner-border" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <div v-else-if="error" class="alert alert-danger">
        {{ error }}
      </div>

      <div v-else class="row">
        <div v-for="product in latestProducts" 
             :key="product.id" 
             class="col-md-3 mb-4">
          <div class="card h-100 shadow-sm">
            <img :src="product.image" 
                 class="card-img-top" 
                 :alt="product.name"
                 style="height: 200px; object-fit: cover;">
            <div class="card-body">
              <h5 class="card-title">{{ product.name }}</h5>
              <p class="card-text text-truncate">{{ product.description }}</p>
              <p class="card-text">
                <strong>${{ product.price }}</strong>
              </p>
            </div>
            <div class="card-footer bg-white border-top-0">
              <router-link :to="`/products/${product.id}`" 
                          class="btn btn-outline-primary w-100">
                View Details
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>

export default {
  name: 'Home',
  
  data() {
    return {
      latestProducts: [],
      loading: true,
      error: null
    }
  },

  methods: {
    async fetchLatestProducts() {
      try {
        console.log('Fetching latest products...');
        const response = await this.$axios.get('/api/products/latest');
        console.log('API Response:', response.data);

        if (response.data.success) {
          this.latestProducts = response.data.products;
          this.error = null;
        } else {
          throw new Error(response.data.message || 'Failed to load products');
        }
      } catch (error) {
        console.error('Error details:', error);
        this.error = error.response?.data?.message || error.message;
      } finally {
        this.loading = false;
      }
    }
  },

  created() {
    this.fetchLatestProducts();
  }
}
</script>

<style scoped>
.product-image-container {
  height: 200px;
  width: 100%;
  overflow: hidden;
  background-color: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.card-img-top {
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: contain;
  transition: transform 0.3s ease;
}

.image-placeholder {
  width: 100%;
  height: 100%;
  background-color: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #adb5bd;
  font-size: 1.5rem;
}

.card {
  transition: all 0.3s ease;
  border: 1px solid rgba(0,0,0,.125);
  overflow: hidden;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.card:hover {
  transform: translateY(-10px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>