<template>
  <div class="container mt-4">
    <h1 class="mb-4">Dashboard</h1>
    
    <div class="row">
      <!-- Stats Cards -->
      <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <h5 class="card-title">Total Orders</h5>
            <h2>{{ stats.totalOrders }}</h2>
            <p class="mb-0">{{ stats.ordersTrend }}% from last month</p>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
          <div class="card-body">
            <h5 class="card-title">Revenue</h5>
            <h2>${{ formatPrice(stats.revenue) }}</h2>
            <p class="mb-0">{{ stats.revenueTrend }}% from last month</p>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-4">
        <div class="card bg-info text-white">
          <div class="card-body">
            <h5 class="card-title">Users</h5>
            <h2>{{ stats.totalUsers }}</h2>
            <p class="mb-0">{{ stats.usersTrend }}% from last month</p>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white">
          <div class="card-body">
            <h5 class="card-title">Products</h5>
            <h2>{{ stats.totalProducts }}</h2>
            <p class="mb-0">{{ stats.productsTrend }}% from last month</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Recent Orders</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in recentOrders" :key="order.id">
                <td>
                  <router-link :to="`/admin/orders/${order.id}`">
                    #{{ order.id }}
                  </router-link>
                </td>
                <td>{{ order.customer_name }}</td>
                <td>${{ formatPrice(order.total) }}</td>
                <td>
                  <span :class="getStatusBadgeClass(order.status)">
                    {{ order.status }}
                  </span>
                </td>
                <td>{{ formatDate(order.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Low Stock Products -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Low Stock Products</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Current Stock</th>
                <th>Min Stock</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in lowStockProducts" :key="product.id">
                <td>{{ product.name }}</td>
                <td>{{ product.stock }}</td>
                <td>{{ product.min_stock }}</td>
                <td>
                  <router-link 
                    :to="`/admin/products/${product.id}/edit`"
                    class="btn btn-sm btn-primary">
                    Update Stock
                  </router-link>
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
export default {
  name: 'AdminDashboard',

  data() {
    return {
      stats: {
        totalOrders: 0,
        ordersTrend: 0,
        revenue: 0,
        revenueTrend: 0,
        totalUsers: 0,
        usersTrend: 0,
        totalProducts: 0,
        productsTrend: 0
      },
      recentOrders: [],
      lowStockProducts: []
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

    async fetchDashboardData() {
      try {
        const response = await this.$axios.get('/admin/dashboard')
        this.stats = response.data.stats
        this.recentOrders = response.data.recentOrders
        this.lowStockProducts = response.data.lowStockProducts
      } catch (error) {
        console.error('Error fetching dashboard data:', error)
        this.$toast.error('Failed to load dashboard data')
      }
    }
  },

  created() {
    this.fetchDashboardData()
  }
}
</script>

<style scoped>
.card {
  transition: transform 0.2s;
}

.card:hover {
  transform: translateY(-5px);
}
</style>