<template>
  <div class="container-fluid mt-4">
    <h1 class="mb-4">Admin Dashboard</h1>
    
    <div class="row">
      <!-- Stats Cards -->
      <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white shadow">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-white-50">Total Orders</h6>
                <h2 class="mb-0">{{ stats.totalOrders }}</h2>
              </div>
              <i class="fas fa-shopping-bag fa-2x text-white-50"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-4">
        <div class="card bg-success text-white shadow">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-white-50">Revenue</h6>
                <h2 class="mb-0">${{ formatPrice(stats.revenue) }}</h2>
              </div>
              <i class="fas fa-dollar-sign fa-2x text-white-50"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-4">
        <div class="card bg-info text-white shadow">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-white-50">Users</h6>
                <h2 class="mb-0">{{ stats.totalUsers }}</h2>
              </div>
              <i class="fas fa-users fa-2x text-white-50"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white shadow">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-white-50">Products</h6>
                <h2 class="mb-0">{{ stats.totalProducts }}</h2>
              </div>
              <i class="fas fa-box fa-2x text-white-50"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="card mb-4 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
        <h5 class="mb-0">Recent Orders</h5>
        <router-link to="/admin/orders" class="btn btn-sm btn-primary">
          View All Orders
        </router-link>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-3">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in recentOrders" :key="order.id">
                <td>
                  <router-link :to="`/admin/orders/${order.id}`" class="fw-bold text-decoration-none">
                    #{{ order.id }}
                  </router-link>
                </td>
                <td>{{ order.username }}</td>
                <td>${{ formatPrice(order.total_amount) }}</td>
                <td>
                  <span :class="getStatusBadgeClass(order.status)">
                    {{ order.status }}
                  </span>
                </td>
                <td>{{ formatDate(order.created_at) }}</td>
                <td>
                  <router-link :to="`/admin/orders/${order.id}`" class="btn btn-sm btn-primary me-2">
                    <i class="fas fa-eye"></i>
                  </router-link>
                </td>
              </tr>
              <tr v-if="recentOrders.length === 0">
                <td colspan="6" class="text-center py-3">No orders found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Products -->
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
        <h5 class="mb-0">Recent Products</h5>
        <router-link to="/admin/products" class="btn btn-sm btn-primary">
          Manage Products
        </router-link>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-3">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in products" :key="product.id">
                <td>
                  <img :src="product.image" :alt="product.name" width="50" height="50" 
                       class="img-thumbnail" style="object-fit: cover;">
                </td>
                <td>{{ product.name }}</td>
                <td>${{ formatPrice(product.price) }}</td>
                <td>
                  <router-link :to="`/admin/products/edit/${product.id}`" class="btn btn-sm btn-primary me-2">
                    <i class="fas fa-edit"></i>
                  </router-link>
                </td>
              </tr>
              <tr v-if="products.length === 0">
                <td colspan="4" class="text-center py-3">No products found</td>
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
        revenue: 0,
        totalUsers: 0,
        totalProducts: 0
      },
      recentOrders: [],
      products: [],
      loading: true
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
        'badge bg-warning text-dark': status === 'pending',
        'badge bg-info text-dark': status === 'processing',
        'badge bg-danger': status === 'cancelled'
      }
    },
    
    async fetchDashboardData() {
      try {
        this.loading = true
        
        // Fetch orders
        const ordersResponse = await fetch('/api/admin/orders')
        const ordersData = await ordersResponse.json()
        if (ordersData.orders) {
          this.recentOrders = ordersData.orders.slice(0, 5)
          this.stats.totalOrders = ordersData.orders.length
          
          // Calculate revenue
          this.stats.revenue = ordersData.orders.reduce((total, order) => {
            return total + Number(order.total_amount)
          }, 0)
        }
        
        // Fetch users
        const usersResponse = await fetch('/api/admin/users')
        const usersData = await usersResponse.json()
        if (usersData.users) {
          this.stats.totalUsers = usersData.users.length
        }
        
        // Fetch products
        const productsResponse = await fetch('/api/products')
        const productsData = await productsResponse.json()
        if (productsData.products) {
          this.products = productsData.products.slice(0, 5)
          this.stats.totalProducts = productsData.products.length
        }
      } catch (error) {
        console.error('Error fetching dashboard data:', error)
      } finally {
        this.loading = false
      }
    }
  },
  
  mounted() {
    this.fetchDashboardData()
  }
}
</script>

<style scoped>
.card {
  transition: all 0.3s ease;
  border: none;
  border-radius: 0.5rem;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.table th {
  font-weight: 600;
  color: #495057;
}

.table td {
  vertical-align: middle;
}

.badge {
  padding: 0.5em 0.75em;
  font-weight: 500;
}

.bg-primary, .bg-success, .bg-info, .bg-warning {
  position: relative;
  overflow: hidden;
}

.bg-primary::after, .bg-success::after, .bg-info::after, .bg-warning::after {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 100px;
  height: 100%;
  background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 100%);
  transform: skewX(-15deg) translateX(50%);
}
</style>