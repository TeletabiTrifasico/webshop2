<template>
  <div class="container mt-4">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">{{ isEditing ? 'Edit Product' : 'Add New Product' }}</h2>
          </div>
          
          <div class="alert alert-danger" v-if="error" role="alert">
            {{ error }}
          </div>

          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" 
                       class="form-control" 
                       id="name" 
                       v-model="form.name"
                       required>
              </div>

              <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" 
                          id="description" 
                          v-model="form.description" 
                          rows="3" 
                          required></textarea>
              </div>

              <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" 
                       class="form-control" 
                       id="price" 
                       v-model="form.price"
                       step="0.01" 
                       required>
              </div>

              <div class="mb-3">
                <label for="image" class="form-label">Product Image</label>
                <div v-if="form.image || previewImage" class="mb-2">
                  <img :src="previewImage || form.image" 
                       alt="Product image preview" 
                       class="img-fluid"
                       style="max-width: 200px;">
                </div>
                <input type="file" 
                       class="form-control" 
                       id="image" 
                       @change="handleImageUpload"
                       :required="!isEditing">
              </div>

              <div class="d-flex justify-content-between">
                <router-link to="/admin/products" 
                            class="btn btn-secondary">
                  Cancel
                </router-link>
                <button type="submit" 
                        class="btn btn-primary"
                        :disabled="loading">
                  {{ loading ? 'Saving...' : (isEditing ? 'Update Product' : 'Add Product') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProductForm',

  data() {
    return {
      form: {
        name: '',
        description: '',
        price: '',
        image: null
      },
      previewImage: null,
      loading: false,
      error: null
    }
  },

  computed: {
    isEditing() {
      return !!this.$route.params.id
    }
  },

  methods: {
    handleImageUpload(event) {
      const file = event.target.files[0]
      if (file) {
        this.form.imageFile = file
        this.previewImage = URL.createObjectURL(file)
      }
    },

    async handleSubmit() {
      this.loading = true
      this.error = null

      try {
        const formData = new FormData()
        formData.append('name', this.form.name)
        formData.append('description', this.form.description)
        formData.append('price', this.form.price)
        if (this.form.imageFile) {
          formData.append('image', this.form.imageFile)
        }

        const url = this.isEditing 
          ? `/admin/products/${this.$route.params.id}`
          : '/admin/products'
        
        const response = await this.$axios.post(url, formData)

        this.$toast.success(
          this.isEditing ? 'Product updated successfully' : 'Product created successfully'
        )
        this.$router.push('/admin/products')
      } catch (error) {
        console.error('Error:', error)
        this.error = error.response?.data?.message || 'An error occurred'
      } finally {
        this.loading = false
      }
    },

    async fetchProduct() {
      try {
        const response = await this.$axios.get(`/admin/products/${this.$route.params.id}`)
        const product = response.data.product
        this.form = {
          name: product.name,
          description: product.description,
          price: product.price,
          image: product.image
        }
      } catch (error) {
        console.error('Error fetching product:', error)
        this.error = 'Failed to load product'
      }
    }
  },

  created() {
    if (this.isEditing) {
      this.fetchProduct()
    }
  },

  beforeUnmount() {
    // Clean up any created object URLs
    if (this.previewImage) {
      URL.revokeObjectURL(this.previewImage)
    }
  }
}
</script>