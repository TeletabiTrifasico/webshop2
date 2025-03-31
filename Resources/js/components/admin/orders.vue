<template>
  <div class="container mt-4">
    <h1 class="mb-4">Orders</h1>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select v-model="statusFilter" class="form-select">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Search</label>
              <input type="text" 
                     v-model="searchQuery" 
                     class="form-control"
                     placeholder="Search by order ID or customer name">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow-sm">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3">Loading orders...</p>
        </div>
        
        <div v-else-if="error" class="alert alert-danger">
          {{ error }}
        </div>
        
        <div v-else-if="filteredOrders.length === 0" class="text-center py-5">
          <div class="mb-4">
            <i class="fas fa-shopping-bag fa-4x text-muted"></i>
          </div>
          <h3>No orders found</h3>
          <p class="text-muted">Try adjusting your search or filters.</p>
        </div>
        
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in filteredOrders" :key="order.id">
                <td>
                  <router-link :to="`/admin/orders/${order.id}`" class="text-decoration-none fw-bold">
                    #{{ order.id }}
                  </router-link>
                </td>
                <td>{{ order.username }}</td>
                <td>{{ formatDate(order.created_at) }}</td>
                <td>${{ formatPrice(order.total_amount) }}</td>
                <td>
                  <span :class="getStatusBadgeClass(order.status)">
                    {{ order.status }}
                  </span>
                </td>
                <td>
                  <div class="btn-group">
                    <router-link 
                      :to="`/admin/orders/${order.id}`"
                      class="btn btn-sm btn-primary me-2">
                      <i class="fas fa-eye"></i> View
                    </router-link>
                    <button 
                      @click="deleteOrder(order.id)"
                      class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i>
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
  name: 'AdminOrders',

  setup() {
    const orders = ref([])
    const loading = ref(true)
    const error = ref(null)
    const searchQuery = ref('')
    const statusFilter = ref('')

    const filteredOrders = computed(() => {
      let result = orders.value

      // Filter by status
      if (statusFilter.value) {
        result = result.filter(order => order.status === statusFilter.value)
      }
      
      // Filter by search query
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        result = result.filter(order => 
          order.id.toString().includes(query) || 
          order.username.toLowerCase().includes(query)
        )
      }
      
      return result
    })

    const formatPrice = (price) => {
      return Number(price).toFixed(2)
    }

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString()
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

    const fetchOrders = async () => {
      try {
        loading.value = true
        const response = await axios.get('/api/admin/orders')
        
        if (response.data.orders) {
          orders.value = response.data.orders
        } else {
          throw new Error('Failed to load orders')
        }
      } catch (err) {
        error.value = err.message || 'An error occurred while loading orders'
        console.error(err)
      } finally {
        loading.value = false
      }
    }

    const deleteOrder = async (id) => {
      if (!confirm('Are you sure you want to delete this order?')) {
        return
      }

      try {
        const response = await axios.delete(`/api/admin/orders/${id}`)
        
        if (response.data.success) {
          // Remove the order from the list
          orders.value = orders.value.filter(o => o.id !== id)
        } else {
          throw new Error(response.data.message || 'Failed to delete order')
        }
      } catch (err) {
        alert(err.message || 'An error occurred while deleting the order')
        console.error(err)
      }
    }

    onMounted(() => {
      fetchOrders()
    })

    return {
      orders,
      filteredOrders,
      loading,
      error,
      searchQuery,
      statusFilter,
      formatPrice,
      formatDate,
      getStatusBadgeClass,
      deleteOrder
    }
  }
}
</script>