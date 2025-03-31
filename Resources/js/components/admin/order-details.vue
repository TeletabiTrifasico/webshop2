<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>Order #{{ orderId }}</h1>
      <router-link to="/admin/orders" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Orders
      </router-link>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3">Loading order details...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <div v-else>
      <!-- Order Information -->
      <div class="row mb-4">
        <div class="col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
              <h5 class="mb-0">Order Information</h5>
            </div>
            <div class="card-body">
              <div class="row mb-2">
                <div class="col-md-4 text-muted">Order ID:</div>
                <div class="col-md-8 fw-bold">{{ order.id }}</div>
              </div>
              <div class="row mb-2">
                <div class="col-md-4 text-muted">Date:</div>
                <div class="col-md-8">{{ formatDate(order.created_at) }}</div>
              </div>
              <div class="row mb-2">
                <div class="col-md-4 text-muted">Status:</div>
                <div class="col-md-8">
                  <span :class="getStatusBadgeClass(order.status)">
                    {{ order.status }}
                  </span>
                </div>
              </div>
              <div class="row mb-2">
                <div class="col-md-4 text-muted">Total:</div>
                <div class="col-md-8 fw-bold">${{ formatPrice(order.total_amount) }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
              <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
              <div class="row mb-2">
                <div class="col-md-4 text-muted">Customer:</div>
                <div class="col-md-8">{{ order.username }}</div>
              </div>
              <div class="row mb-2">
                <div class="col-md-4 text-muted">Email:</div>
                <div class="col-md-8">{{ order.email }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Items -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
          <h5 class="mb-0">Order Items</h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Price</th>
                  <th>Quantity</th>
                  <th class="text-end">Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in orderItems" :key="item.id">
                  <td>
                    <div class="d-flex align-items-center">
                      <img 
                        v-if="item.product_image" 
                        :src="item.product_image" 
                        :alt="item.product_name"
                        class="me-3" 
                        style="width: 50px; height: 50px; object-fit: cover;">
                      <div>
                        <strong>{{ item.product_name }}</strong>
                      </div>
                    </div>
                  </td>
                  <td>${{ formatPrice(item.price) }}</td>
                  <td>{{ item.quantity }}</td>
                  <td class="text-end">${{ formatPrice(item.price * item.quantity) }}</td>
                </tr>
              </tbody>
              <tfoot class="table-group-divider">
                <tr>
                  <td colspan="3" class="text-end fw-bold">Total:</td>
                  <td class="text-end fw-bold">${{ formatPrice(order.total_amount) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- Update Status -->
      <div class="card shadow-sm">
        <div class="card-header bg-white">
          <h5 class="mb-0">Update Order Status</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <select v-model="newStatus" class="form-select mb-3">
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-6">
              <button 
                @click="updateOrderStatus" 
                class="btn btn-primary" 
                :disabled="statusLoading || newStatus === order.status">
                <span v-if="statusLoading">
                  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  Updating...
                </span>
                <span v-else>Update Status</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

export default {
  name: 'OrderDetails',

  setup() {
    const route = useRoute()
    const router = useRouter()
    
    const orderId = computed(() => route.params.id)
    const loading = ref(true)
    const statusLoading = ref(false)
    const error = ref(null)
    const order = ref({})
    const orderItems = ref([])
    const newStatus = ref('')

    const formatPrice = (price) => {
      return Number(price).toFixed(2)
    }

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleString()
    }

    const getStatusBadgeClass = (status) => {
      const classes = {
        'pending': 'badge bg-warning text-dark',
        'processing': 'badge bg-info text-dark',
        'shipped': 'badge bg-primary',
        'delivered': 'badge bg-success',
        'cancelled': 'badge bg-danger'
      }
      return classes[status] || 'badge bg-secondary'
    }

    const fetchOrderDetails = async () => {
      try {
        loading.value = true
        const response = await axios.get(`/api/admin/orders/${orderId.value}`)
        
        if (response.data.order) {
          order.value = response.data.order
          orderItems.value = response.data.items || []
          newStatus.value = order.value.status
        } else {
          throw new Error('Order not found')
        }
      } catch (err) {
        error.value = err.message || 'Failed to load order details'
        console.error(err)
      } finally {
        loading.value = false
      }
    }

    const updateOrderStatus = async () => {
      if (newStatus.value === order.value.status) return

      try {
        statusLoading.value = true
        const response = await axios.put(`/api/admin/orders/${orderId.value}/status`, {
          status: newStatus.value
        })
        
        if (response.data.success) {
          order.value.status = newStatus.value
        } else {
          throw new Error(response.data.message || 'Failed to update order status')
        }
      } catch (err) {
        alert(err.message || 'An error occurred while updating the order status')
        console.error(err)
      } finally {
        statusLoading.value = false
      }
    }

    onMounted(() => {
      fetchOrderDetails()
    })

    return {
      orderId,
      order,
      orderItems,
      loading,
      statusLoading,
      error,
      newStatus,
      formatPrice,
      formatDate,
      getStatusBadgeClass,
      updateOrderStatus
    }
  }
}
</script>