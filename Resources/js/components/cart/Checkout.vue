<template>
  <div class="container mt-4">
    <h1 class="mb-4">Checkout</h1>
    
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3">Processing your order...</p>
    </div>
    
    <div v-else class="row">
      <div class="col-md-8">
        <div class="card mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0">Shipping Information</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="placeOrder">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="firstName" class="form-label">First Name</label>
                  <input type="text" class="form-control" id="firstName" v-model="form.firstName" required>
                </div>
                <div class="col-md-6">
                  <label for="lastName" class="form-label">Last Name</label>
                  <input type="text" class="form-control" id="lastName" v-model="form.lastName" required>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" v-model="form.address" required>
              </div>
              
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="city" class="form-label">City</label>
                  <input type="text" class="form-control" id="city" v-model="form.city" required>
                </div>
                <div class="col-md-4">
                  <label for="state" class="form-label">State</label>
                  <input type="text" class="form-control" id="state" v-model="form.state" required>
                </div>
                <div class="col-md-2">
                  <label for="zip" class="form-label">ZIP</label>
                  <input type="text" class="form-control" id="zip" v-model="form.zip" required>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" v-model="form.email" required>
              </div>
              
              <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="tel" class="form-control" id="phone" v-model="form.phone" required>
              </div>
              
              <div class="mb-4">
                <label for="notes" class="form-label">Order Notes (Optional)</label>
                <textarea class="form-control" id="notes" v-model="form.notes" rows="3"></textarea>
              </div>
              
              <button type="submit" class="btn btn-primary" :disabled="loading">
                Place Order
              </button>
              <router-link to="/cart" class="btn btn-outline-secondary ms-2">
                Back to Cart
              </router-link>
            </form>
          </div>
        </div>
      </div>
      
      <div class="col-md-4">
        <div class="card">
          <div class="card-header bg-white">
            <h5 class="mb-0">Order Summary</h5>
          </div>
          <div class="card-body">
            <div v-for="item in cartItems" :key="item.id" class="d-flex justify-content-between mb-2">
              <span>{{ item.product.name }} × {{ item.quantity }}</span>
              <span>${{ formatPrice(item.product.price * item.quantity) }}</span>
            </div>
            <hr>
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
    
    const form = ref({
      firstName: '',
      lastName: '',
      address: '',
      city: '',
      state: '',
      zip: '',
      email: '',
      phone: '',
      notes: ''
    })
    
    // Pre-fill form with user data if available
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
        const result = await store.dispatch('cart/checkout')
        
        if (result.success) {
          router.push(`/order-success/${result.orderId}`)
        } else {
          error.value = result.error || 'Failed to place order'
        }
      } catch (err) {
        console.error('Checkout error:', err)
        error.value = 'An error occurred while processing your order'
      } finally {
        loading.value = false
      }
    }
    
    onMounted(() => {
      if (cartItems.value.length === 0) {
        router.push('/cart')
      }
      
      // Pre-fill with user data if available
      if (user.value) {
        form.value.email = user.value.email
        form.value.firstName = user.value.username
      }
    })
    
    return {
      loading,
      error,
      form,
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