<template>
  <div class="container mt-4">
    <h1 class="mb-4">Shopping Cart</h1>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3">Loading your cart...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <div v-else-if="!items.length" class="text-center py-5">
      <div class="mb-4">
        <i class="fas fa-shopping-cart fa-4x text-muted"></i>
      </div>
      <h3>Your cart is empty</h3>
      <p class="text-muted">Add items to your cart to see them here.</p>
      <router-link to="/products" class="btn btn-primary mt-3">
        Browse Products
      </router-link>
    </div>

    <div v-else class="row">
      <div class="col-md-8">
        <div class="card mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0">Cart Items ({{ items.length }})</h5>
          </div>
          <div class="card-body p-0">
            <div class="list-group list-group-flush">
              <div v-for="item in items" 
                   :key="item.id" 
                   class="list-group-item py-3">
                <div class="row align-items-center">
                  <div class="col-md-2">
                    <img :src="item.product.image" 
                         :alt="item.product.name"
                         class="img-fluid rounded">
                  </div>
                  <div class="col-md-5">
                    <h5 class="mb-1">{{ item.product.name }}</h5>
                    <p class="mb-0 text-muted">
                      ${{ formatPrice(item.product.price) }} each
                    </p>
                  </div>
                  <div class="col-md-3">
                    <div class="input-group">
                      <button @click="updateQuantity(item.id, item.quantity - 1)"
                              class="btn btn-outline-secondary"
                              :disabled="item.quantity <= 1 || itemLoading">
                        <i class="fas fa-minus"></i>
                      </button>
                      <input type="number"
                             v-model.number="item.quantity"
                             @change="updateQuantity(item.id, item.quantity)"
                             class="form-control text-center"
                             min="1">
                      <button @click="updateQuantity(item.id, item.quantity + 1)"
                              class="btn btn-outline-secondary"
                              :disabled="itemLoading">
                        <i class="fas fa-plus"></i>
                      </button>
                    </div>
                  </div>
                  <div class="col-md-2 text-end">
                    <div class="d-flex flex-column align-items-end">
                      <span class="fw-bold mb-2">
                        ${{ formatPrice(item.product.price * item.quantity) }}
                      </span>
                      <button @click="removeFromCart(item.id)"
                              class="btn btn-sm btn-outline-danger"
                              :disabled="itemLoading">
                        <i class="fas fa-trash-alt me-1"></i> Remove
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
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

            <button @click="checkout"
                    class="btn btn-primary w-100"
                    :disabled="itemLoading">
              <i class="fas fa-credit-card me-2"></i>
              Proceed to Checkout
            </button>

            <button @click="clearCart"
                    class="btn btn-outline-secondary w-100 mt-2"
                    :disabled="itemLoading">
              <i class="fas fa-trash me-2"></i>
              Clear Cart
            </button>
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
  name: 'ShoppingCart',

  setup() {
    const store = useStore()
    const router = useRouter()
    
    const loading = ref(true)
    const itemLoading = ref(false)
    const error = ref(null)
    
    const items = computed(() => store.state.cart.items)
    
    const subtotal = computed(() => {
      return items.value.reduce((sum, item) => {
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
    
    const fetchCart = async () => {
      try {
        loading.value = true
        await store.dispatch('cart/fetchCart')
      } catch (err) {
        error.value = 'Failed to load cart'
      } finally {
        loading.value = false
      }
    }
    
    const updateQuantity = async (itemId, quantity) => {
      if (quantity < 1) return
      
      try {
        itemLoading.value = true
        await store.dispatch('cart/updateItem', { itemId, quantity })
      } catch (err) {
        error.value = 'Failed to update quantity'
      } finally {
        itemLoading.value = false
      }
    }
    
    const removeFromCart = async (itemId) => {
      if (confirm('Are you sure you want to remove this item?')) {
        try {
          itemLoading.value = true
          await store.dispatch('cart/removeItem', itemId)
        } catch (err) {
          error.value = 'Failed to remove item'
        } finally {
          itemLoading.value = false
        }
      }
    }
    
    const clearCart = async () => {
      if (confirm('Are you sure you want to clear your cart?')) {
        try {
          itemLoading.value = true
          await store.dispatch('cart/clearCart')
        } catch (err) {
          error.value = 'Failed to clear cart'
        } finally {
          itemLoading.value = false
        }
      }
    }
    
    const checkout = async () => {
      try {
        itemLoading.value = true
        const result = await store.dispatch('cart/checkout')
        
        if (result.success) {
          router.push(`/order-success/${result.orderId}`)
        } else {
          error.value = result.error || 'Checkout failed'
        }
      } catch (err) {
        error.value = 'Failed to complete checkout'
      } finally {
        itemLoading.value = false
      }
    }
    
    onMounted(() => {
      fetchCart()
    })
    
    return {
      items,
      loading,
      itemLoading,
      error,
      subtotal,
      shipping,
      total,
      formatPrice,
      updateQuantity,
      removeFromCart,
      clearCart,
      checkout
    }
  }
}
</script>

<style scoped>
.input-group {
  width: 120px;
}

input[type="number"] {
  -moz-appearance: textfield;
}

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>