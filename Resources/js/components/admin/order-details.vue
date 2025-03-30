<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>Order #{{ orderId }}</h1>
      <router-link to="/admin/orders" class="btn btn-secondary">
        Back to Orders
      </router-link>
    </div>

    <div v-if="order" class="row">
      <!-- Order Status -->
      <div class="col-md-12 mb-4">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="mb-0">Status: 
                  <span :class="getStatusBadgeClass(order.status)">
                    {{ order.status }}
                  </span>
                </h5>
              </div>
              <div class="d-flex gap-2">
                <select v-model="newStatus" class="form-select w-auto">
                  <option value="pending">Pending</option>
                  <option value="processing">Processing</option>
                  <option value="completed">Completed</option>
                  <option value="cancelled">Cancelled</option>
                </select>
                <button 
                  @click="updateStatus"
                  class="btn btn-primary"
                  :disabled="newStatus === order.status">
                  Update Status
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Customer Details -->
      <div class="col-md-6 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Customer Information</h5>
          </div>
          <div class="card-body">
            <p><strong>Name:</strong> {{ order.customer.name }}</p>
            <p><strong>Email:</strong> {{ order.customer.email }}</p>
            <p><strong>Phone:</strong> {{ order.customer.phone }}</p>
          </div>
        </div>
      </div>

      <!-- Shipping Address -->
      <div class="col-md-6 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Shipping Address</h5>
          </div>
          <div class="card-body">
            <p>{{ order.shipping_address.street }}</p>
            <p>{{ order.shipping_address.city }}, 
               {{ order.shipping_address.state }} 
               {{ order.shipping_address.zip }}</p>
            <p>{{ order.shipping_address.country }}</p>
          </div>
        </div>
      </div>

      <!-- Order Items -->
      <div class="col-md-12 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Order Items</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th class="text-end">Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in order.items" :key="item.id">
                    <td>{{ item.product.name }}</td>
                    <td>${{ formatPrice(item.price) }}</td>
                    <td>{{ item.quantity }}</td>
                    <td class="text-end">
                      ${{ formatPrice(item.price * item.quantity) }}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3" class="text-end"><strong>Subtotal</strong></td>
                    <td class="text-end">${{ formatPrice(order.subtotal) }}</td>
                  </tr>
                  <tr>
                    <td colspan="3" class="text-end"><strong>Shipping</strong></td>
                    <td class="text-end">${{ formatPrice(order.shipping) }}</td>
                  </tr>
                  <tr>
                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                    <td class="text-end">${{ formatPrice(order.total) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Order History -->
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Order History</h5>
          </div>
          <div class="card-body">
            <ul class="list-group">
              <li v-for="history in order.history" 
                  :key="history.id"
                  class="list-group-item">
                <div class="d-flex justify-content-between">
                  <span>{{ history.message }}</span>
                  <small>{{ formatDate(history.created_at) }}</small>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center">
      <p>Loading order details...</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminOrderDetails',

  data() {
    return {
      orderId: this.$route.params.id,
      order: null,
      newStatus: ''
    }
  },

  methods: {
    formatPrice(price) {
      return Number(price).toFixed(2)
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString()
    },

    getStatusBadgeClass(status) {
      return {
        'badge bg-success': status === 'completed',
        'badge bg-warning': status === 'pending',
        'badge bg-info': status === 'processing',
        'badge bg-danger': status === 'cancelled'
      }
    },

    async fetchOrder() {
      try {
        const response = await this.$axios.get(`/admin/orders/${this.orderId}`)
        this.order = response.data.order
        this.newStatus = this.order.status
      } catch (error) {
        console.error('Error fetching order:', error)
        this.$toast.error('Failed to load order details')
      }
    },

    async updateStatus() {
      try {
        await this.$axios.put(`/admin/orders/${this.orderId}/status`, {
          status: this.newStatus
        })
        this.$toast.success('Order status updated')
        await this.fetchOrder()
      } catch (error) {
        console.error('Error updating status:', error)
        this.$toast.error('Failed to update order status')
      }
    }
  },

  created() {
    this.fetchOrder()
  }
}
</script>