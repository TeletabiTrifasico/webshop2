<template>
  <div class="container mt-4">
    <h1>Shopping Cart</h1>

    <div v-if="items.length" class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <div v-for="item in items" 
                 :key="item.id" 
                 class="cart-item mb-3">
              <div class="row align-items-center">
                <div class="col-2">
                  <img :src="item.product.image" 
                       :alt="item.product.name" 
                       class="img-fluid rounded">
                </div>
                <div class="col">
                  <h5>{{ item.product.name }}</h5>
                  <p class="mb-0">${{ formatPrice(item.product.price) }}</p>
                </div>
                <div class="col-3">
                  <div class="input-group">
                    <button class="btn btn-outline-secondary" 
                            @click="updateQuantity(item.id, item.quantity - 1)"
                            :disabled="item.quantity <= 1">
                      -
                    </button>
                    <input type="number" 
                           v-model.number="item.quantity" 
                           class="form-control text-center"
                           min="1">
                    <button class="btn btn-outline-secondary"
                            @click="updateQuantity(item.id, item.quantity + 1)">
                      +
                    </button>
                  </div>
                </div>
                <div class="col-2 text-end">
                  <button class="btn btn-danger btn-sm"
                          @click="removeItem(item.id)">
                    Remove
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Order Summary</h5>
            <div class="d-flex justify-content-between mb-2">
              <span>Subtotal:</span>
              <span>${{ formatPrice(subtotal) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Shipping:</span>
              <span>${{ formatPrice(shipping) }}</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-3">
              <strong>Total:</strong>
              <strong>${{ formatPrice(total) }}</strong>
            </div>
            <button class="btn btn-primary w-100"
                    @click="checkout">
              Proceed to Checkout
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center">
      <p>Your cart is empty</p>
      <router-link to="/products" class="btn btn-primary">
        Continue Shopping
      </router-link>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'

export default {
  name: 'Cart',

  computed: {
    ...mapState('cart', ['items']),

    subtotal() {
      return this.items.reduce((sum, item) => {
        return sum + (item.product.price * item.quantity)
      }, 0)
    },

    shipping() {
      return this.subtotal > 50 ? 0 : 5.99
    },

    total() {
      return this.subtotal + this.shipping
    }
  },

  methods: {
    ...mapActions('cart', ['updateItem', 'removeItem']),

    formatPrice(price) {
      return Number(price).toFixed(2)
    },

    async updateQuantity(itemId, quantity) {
      if (quantity < 1) return

      try {
        await this.updateItem({ itemId, quantity })
        this.$toast.success('Cart updated')
      } catch (error) {
        console.error('Error updating cart:', error)
        this.$toast.error('Failed to update cart')
      }
    },

    async checkout() {
      try {
        const response = await this.$axios.post('/orders/create')
        this.$router.push(`/orders/${response.data.orderId}`)
      } catch (error) {
        console.error('Error creating order:', error)
        this.$toast.error('Failed to create order')
      }
    }
  }
}
</script>

<style scoped>
.cart-item {
  border-bottom: 1px solid #dee2e6;
  padding-bottom: 1rem;
}

.cart-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.cart-item img {
  max-height: 80px;
  width: auto;
}
</style>