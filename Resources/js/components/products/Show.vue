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
                :disabled="!isAuthenticated || cartLoading">
          <span v-if="cartLoading">Adding...</span>
          <span v-else>Add to Cart</span>
        </button>

        <div v-if="cartMessage" class="alert alert-success mt-3">
          {{ cartMessage }}
        </div>

        <div v-if="!isAuthenticated" class="alert alert-warning mt-3">
          Please <router-link to="/auth/login">login</router-link> to add items to cart
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useRoute, useRouter } from 'vue-router'

export default {
  name: 'ProductShow',

  setup() {
    const store = useStore()
    const route = useRoute()
    const router = useRouter()

    const product = ref(null)
    const quantity = ref(1)
    const loading = ref(true)
    const error = ref(null)
    const cartLoading = ref(false)
    const cartMessage = ref('')

    const isAuthenticated = computed(() => !!store.state.auth.user)

    const formatPrice = (price) => {
      return Number(price).toFixed(2)
    }

    const fetchProduct = async () => {
      try {
        loading.value = true
        const response = await fetch(`/api/products/${route.params.id}`)
        const data = await response.json()
        
        if (data.product) {
          product.value = data.product
        } else {
          throw new Error(data.error || 'Failed to load product')
        }
      } catch (err) {
        error.value = 'Failed to load product'
      } finally {
        loading.value = false
      }
    }

    const addToCart = async () => {
      if (!isAuthenticated.value) {
        router.push('/auth/login')
        return
      }

      try {
        cartLoading.value = true
        const result = await store.dispatch('cart/addItem', {
          productId: product.value.id,
          quantity: quantity.value
        })
        
        if (result.success) {
          cartMessage.value = `${product.value.name} added to your cart!`
          setTimeout(() => {
            cartMessage.value = ''
          }, 5000)
        } else {
          throw new Error(result.error || 'Failed to add item to cart')
        }
      } catch (error) {
        alert('Failed to add product to cart: ' + error.message)
      } finally {
        cartLoading.value = false
      }
    }

    onMounted(() => {
      fetchProduct()
    })

    return {
      product,
      quantity,
      loading,
      cartLoading,
      error,
      cartMessage,
      isAuthenticated,
      formatPrice,
      addToCart
    }
  }
}
</script>