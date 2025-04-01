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
        <div class="card h-100 shadow-sm">
          <div class="product-image-container">
            <img v-if="product.image" 
                 :src="product.image" 
                 class="card-img-top" 
                 :alt="product.name"
                 style="height: 200px; object-fit: cover;">
            <div v-else class="image-placeholder"></div>
          </div>
          <div class="card-body">
            <h5 class="card-title">{{ product.name }}</h5>
            <p class="card-text text-truncate" v-if="product.description">{{ product.description }}</p>
            <p class="card-text">
              <strong>${{ formatPrice(product.price) }}</strong>
            </p>
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
import { onMounted, ref } from 'vue'

export default {
  name: 'ProductIndex',
  
  setup() {
    const products = ref([])
    const loading = ref(true)
    const error = ref(null)

    const formatPrice = (price) => {
      return Number(price).toFixed(2)
    }

    const fetchProducts = async () => {
      try {
        loading.value = true
        const response = await fetch('/api/products')
        const data = await response.json()
        
        console.log('Products response:', data)
        
        if (data.products) {
          products.value = data.products
        } else {
          throw new Error(data.error || 'Failed to load products')
        }
      } catch (err) {
        console.error('Error fetching products:', err)
        error.value = 'Failed to load products'
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchProducts()
    })

    return {
      products,
      loading,
      error,
      formatPrice
    }
  }
}
</script>