<template>
  <div class="container mt-4">
    <h1 class="mb-4">Order Confirmation</h1>
    
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3">Processing your order...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger">
      {{ error }}
    </div>
    
    <div v-else class="row">
      <div class="col-md-7">
        <div class="card mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0">Order Items</h5>
          </div>
          <div class="card-body p-0">
            <div class="list-group list-group-flush">
              <div v-for="item in cartItems" 
                   :key="item.id" 
                   class="list-group-item py-3">
                <div class="row align-items-center">
                  <div class="col-md-2">
                    <img :src="item.product.image" 
                         :alt="item.product.name"
                         class="img-fluid rounded">
                  </div>
                  <div class="col-md-6">
                    <h5 class="mb-1">{{ item.product.name }}</h5>
                    <p class="mb-0 text-muted">
                      ${{ formatPrice(item.product.price) }} each
                    </p>
                  </div>
                  <div class="col-md-2 text-center">
                    <p class="mb-0">Qty: {{ item.quantity }}</p>
                  </div>
                  <div class="col-md-2 text-end">
                    <p class="fw-bold mb-0">
                      ${{ formatPrice(item.product.price * item.quantity) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-5">
        <div class="card">
          <div class="card-header bg-white">
            <h5 class="mb-0">Order Summary</h5>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
              <span>Subtotal:</span>
              <span>${{ formatPrice(subtotal) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Shipping:</span>
              <span v-if="shipping > 0">${{ formatPrice(shipping) }}</span>
              <span v-else class="text-success">Free</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-3">
              <strong>Total:</strong>
              <strong>${{ formatPrice(total) }}</strong>
            </div>
            
            <div class="alert alert-info mb-4">
              <p class="mb-0"><strong>Note:</strong> By confirming this order, you agree to the terms and conditions.</p>
            </div>
            
            <div class="d-grid gap-2">
              <button class="btn btn-primary" 
                      @click="placeOrder" 
                      :disabled="loading || cartItems.length === 0">
                <span v-if="loading">
                  <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                  Processing...
                </span>
                <span v-else>
                  <i class="fas fa-check-circle me-2"></i>
                  Confirm Order
                </span>
              </button>
              
              <router-link to="/cart" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Cart
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'

export default {
  name: 'Checkout',
  
  setup() {
    const store = useStore()
    const router = useRouter()
    
    const loading = ref(false)
    const error = ref(null)
    
    const cartItems = computed(() => store.state.cart.items)
    const user = computed(() => store.state.auth.user)
    
    const subtotal = computed(() => {
      return cartItems.value.reduce((sum, item) => {
        return sum + (Number(item.product.price) * item.quantity)
      }, 0)
    })
    
    const shipping = computed(() => {
      // Free shipping for orders over $50
      return subtotal.value >= 50 ? 0 : 4.99
    })
    
    const total = computed(() => {
      return subtotal.value + shipping.value
    })
    
    const formatPrice = (price) => {
      return Number(price).toFixed(2)
    }
    
    const placeOrder = async () => {
      try {
        loading.value = true
        error.value = null
        
        // Simple checkout data - no shipping info needed
        const checkoutData = { 
          userId: user.value?.id
        }
        
        const result = await store.dispatch('cart/checkout', checkoutData)
        
        if (result.success) {
          router.push(`/order-success/${result.orderId}`)
        } else {
          error.value = result.error || 'Failed to place order'
        }
      } catch (err) {
        console.error('Checkout error:', err)
        error.value = err.message || 'An error occurred while processing your order'
      } finally {
        loading.value = false
      }
    }
    
    onMounted(() => {
      if (cartItems.value.length === 0) {
        router.push('/cart')
        return
      }
    })
    
    return {
      loading,
      error,
      cartItems,
      subtotal,
      shipping,
      total,
      formatPrice,
      placeOrder
    }
  }
}
</script>