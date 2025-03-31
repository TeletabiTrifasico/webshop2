<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>Products</h1>
      <router-link to="/admin/products/create" class="btn btn-success">
        <i class="fas fa-plus me-2"></i> Add New Product
      </router-link>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Search</label>
              <input type="text" 
                     v-model="searchQuery" 
                     class="form-control"
                     placeholder="Search products...">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow-sm">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3">Loading products...</p>
        </div>
        
        <div v-else-if="error" class="alert alert-danger">
          {{ error }}
        </div>
        
        <div v-else-if="filteredProducts.length === 0" class="text-center py-5">
          <div class="mb-4">
            <i class="fas fa-box-open fa-4x text-muted"></i>
          </div>
          <h3>No products found</h3>
          <p class="text-muted">Try adjusting your search or add a new product.</p>
        </div>
        
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in filteredProducts" :key="product.id">
                <td width="80">
                  <img :src="product.image" 
                       :alt="product.name"
                       class="img-thumbnail" 
                       style="width: 50px; height: 50px; object-fit: cover;">
                </td>
                <td>{{ product.name }}</td>
                <td>${{ formatPrice(product.price) }}</td>
                <td>
                  <div class="btn-group">
                    <router-link 
                      :to="`/admin/products/edit/${product.id}`"
                      class="btn btn-sm btn-primary me-2">
                      <i class="fas fa-edit"></i> Edit
                    </router-link>
                    <button 
                      @click="deleteProduct(product.id)"
                      class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i> Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

export default {
  name: 'AdminProducts',

  setup() {
    const products = ref([])
    const loading = ref(true)
    const error = ref(null)
    const searchQuery = ref('')

    const filteredProducts = computed(() => {
      if (!searchQuery.value) return products.value
      
      const query = searchQuery.value.toLowerCase()
      return products.value.filter(product => 
        product.name.toLowerCase().includes(query) || 
        product.description?.toLowerCase().includes(query)
      )
    })

    const formatPrice = (price) => {
      return Number(price).toFixed(2)
    }

    const fetchProducts = async () => {
      try {
        loading.value = true
        const response = await axios.get('/api/products')
        
        if (response.data.products) {
          products.value = response.data.products
        } else {
          throw new Error('Failed to load products')
        }
      } catch (err) {
        error.value = err.message || 'An error occurred while loading products'
        console.error(err)
      } finally {
        loading.value = false
      }
    }

    const deleteProduct = async (id) => {
      if (!confirm('Are you sure you want to delete this product?')) {
        return
      }

      try {
        loading.value = true
        const response = await axios.delete(`/api/admin/products/${id}`)
        
        if (response.data.success) {
          // Remove the product from the list
          products.value = products.value.filter(p => p.id !== id)
        } else {
          throw new Error(response.data.message || 'Failed to delete product')
        }
      } catch (err) {
        alert(err.message || 'An error occurred while deleting the product')
        console.error(err)
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchProducts()
    })

    return {
      products,
      filteredProducts,
      loading,
      error,
      searchQuery,
      formatPrice,
      deleteProduct
    }
  }
}
</script>