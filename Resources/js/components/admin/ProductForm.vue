<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>{{ isEditing ? 'Edit Product' : 'Add New Product' }}</h1>
      <router-link to="/admin/products" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Products
      </router-link>
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        <form @submit.prevent="saveProduct">
          <div class="row">
            <div class="col-md-8">
              <div class="mb-3">
                <label for="productName" class="form-label">Product Name</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="productName" 
                  v-model="product.name" 
                  required
                >
              </div>

              <div class="mb-3">
                <label for="productDescription" class="form-label">Description</label>
                <textarea 
                  class="form-control" 
                  id="productDescription" 
                  v-model="product.description" 
                  rows="5"
                ></textarea>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="productPrice" class="form-label">Price ($)</label>
                    <input 
                      type="number" 
                      class="form-control" 
                      id="productPrice" 
                      v-model="product.price" 
                      min="0.01" 
                      step="0.01" 
                      required
                    >
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="mb-3">
                <label for="productImage" class="form-label">Image URL</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="productImage" 
                  v-model="product.image"
                  placeholder="https://example.com/image.jpg"
                >
              </div>

              <div class="mt-3 mb-4">
                <label class="form-label d-block">Image Preview</label>
                <div 
                  class="border rounded p-2 bg-light text-center image-preview"
                >
                  <img 
                    v-if="product.image" 
                    :src="product.image" 
                    alt="Product preview" 
                    class="img-fluid preview-image"
                  >
                  <div v-else class="placeholder-text">
                    No image available
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <button 
              type="button" 
              class="btn btn-outline-secondary me-2" 
              @click="$router.push('/admin/products')"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="btn btn-primary" 
              :disabled="loading"
            >
              <span v-if="loading">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Saving...
              </span>
              <span v-else>Save Product</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

export default {
  name: 'ProductForm',

  setup() {
    const route = useRoute()
    const router = useRouter()
    const productId = computed(() => route.params.id)
    const isEditing = computed(() => !!productId.value)
    const loading = ref(false)
    const error = ref(null)

    const product = ref({
      name: '',
      description: '',
      price: '',
      image: ''
    })

    const fetchProduct = async () => {
      if (!isEditing.value) return

      try {
        loading.value = true
        const response = await axios.get(`/api/products/${productId.value}`)
        if (response.data.product) {
          product.value = response.data.product
        }
      } catch (err) {
        error.value = 'Failed to load product'
        console.error(err)
      } finally {
        loading.value = false
      }
    }

    const saveProduct = async () => {
      try {
        loading.value = true
        let response

        if (isEditing.value) {
          response = await axios.put(`/api/admin/products/${productId.value}`, product.value)
        } else {
          response = await axios.post('/api/admin/products', product.value)
        }

        if (response.data.success) {
          router.push('/admin/products')
        } else {
          throw new Error(response.data.message || 'Failed to save product')
        }
      } catch (err) {
        error.value = err.message || 'An error occurred while saving the product'
        console.error(err)
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchProduct()
    })

    return {
      product,
      isEditing,
      loading,
      error,
      saveProduct
    }
  }
}
</script>

<style scoped>
.image-preview {
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.preview-image {
  max-height: 100%;
  max-width: 100%;
  object-fit: contain;
}

.placeholder-text {
  color: #adb5bd;
  font-size: 0.9rem;
}
</style>