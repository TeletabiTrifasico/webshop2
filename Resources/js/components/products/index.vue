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

    <!-- Add pagination controls -->
    <nav v-if="!loading && !error && pagination.last_page > 1" class="mt-4">
      <ul class="pagination justify-content-center">
        <li :class="['page-item', { disabled: pagination.current_page === 1 }]">
          <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">
            Previous
          </a>
        </li>
        
        <li v-for="page in displayedPages" :key="page" 
            :class="['page-item', { active: page === pagination.current_page }]">
          <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
        </li>
        
        <li :class="['page-item', { disabled: pagination.current_page === pagination.last_page }]">
          <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">
            Next
          </a>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

export default {
  name: 'ProductIndex',
  
  setup() {
    const products = ref([])
    const loading = ref(true)
    const error = ref(null)
    const searchQuery = ref('')
    const pagination = ref({
      current_page: 1,
      per_page: 10,
      total: 0,
      last_page: 1
    })
    
    const route = useRoute()
    const router = useRouter()
    
    const formatPrice = (price) => {
      return Number(price).toFixed(2)
    }

    // Calculate which page numbers to display
    const displayedPages = computed(() => {
      const pages = []
      let startPage = Math.max(1, pagination.value.current_page - 2)
      let endPage = Math.min(pagination.value.last_page, pagination.value.current_page + 2)
      
      // Always show at least 5 pages if available
      if (endPage - startPage < 4 && pagination.value.last_page > 5) {
        if (startPage === 1) {
          endPage = Math.min(5, pagination.value.last_page)
        } else {
          startPage = Math.max(1, pagination.value.last_page - 4)
        }
      }
      
      for (let i = startPage; i <= endPage; i++) {
        pages.push(i)
      }
      
      return pages
    })
    
    const fetchProducts = async () => {
      try {
        loading.value = true
        
        // Get page from route query or default to 1
        const page = parseInt(route.query.page) || 1
        const searchTerm = route.query.search || ''
        searchQuery.value = searchTerm
        
        const response = await axios.get('/api/products', {
          params: {
            page: page,
            limit: 10,
            search: searchTerm
          }
        })
        
        if (response.data.success) {
          products.value = response.data.products
          pagination.value = response.data.pagination
        } else {
          throw new Error(response.data.error || 'Failed to load products')
        }
      } catch (err) {
        error.value = err.message || 'An error occurred while loading products'
        console.error(err)
      } finally {
        loading.value = false
      }
    }
    
    const changePage = (page) => {
      if (page < 1 || page > pagination.value.last_page) return
      
      // Update URL with query parameters
      router.push({
        query: { 
          ...route.query,
          page: page
        }
      })
    }
    
    const handleSearch = () => {
      router.push({
        query: { 
          search: searchQuery.value,
          page: 1
        }
      })
    }
    
    // Watch for route changes to fetch products
    watch(() => route.query, () => {
      fetchProducts()
    }, { immediate: true })
    
    onMounted(() => {
      fetchProducts()
    })

    return {
      products,
      loading,
      error,
      pagination,
      displayedPages,
      searchQuery,
      changePage,
      handleSearch,
      formatPrice
    }
  }
}
</script>