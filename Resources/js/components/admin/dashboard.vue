<template>
  <div class="container-fluid mt-4">
    <h1 class="admin-title mb-4">Admin Dashboard</h1>
    
    <div class="row">
      <!-- Stats Cards -->
      <div class="col-md-3 mb-4">
        <div class="card stat-card bg-primary shadow admin-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="stat-title">Total Orders</h6>
                <h2 class="stat-value mb-0">{{ stats.totalOrders }}</h2>
              </div>
              <i class="fas fa-shopping-bag stat-icon"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-4">
        <div class="card stat-card bg-success shadow admin-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="stat-title">Revenue</h6>
                <h2 class="stat-value mb-0">${{ formatPrice(stats.revenue) }}</h2>
              </div>
              <i class="fas fa-dollar-sign stat-icon"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-4">
        <div class="card stat-card bg-info shadow admin-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="stat-title">Users</h6>
                <h2 class="stat-value mb-0">{{ stats.totalUsers }}</h2>
              </div>
              <i class="fas fa-users stat-icon"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3 mb-4">
        <div class="card stat-card bg-warning shadow admin-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="stat-title">Products</h6>
                <h2 class="stat-value mb-0">{{ stats.totalProducts }}</h2>
              </div>
              <i class="fas fa-box stat-icon"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="card admin-card shadow-sm">
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
          <table class="table admin-table table-hover">
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
                  <router-link :to="`/admin/orders/${order.id}`" class="order-id">
                    #{{ order.id }}
                  </router-link>
                </td>
                <td>{{ order.username }}</td>
                <td>${{ formatPrice(order.total_amount) }}</td>
                <td>
                  <span class="badge" :class="getStatusBadgeClass(order.status)">
                    {{ order.status }}
                  </span>
                </td>
                <td>{{ formatDate(order.created_at) }}</td>
                <td>
                  <router-link :to="`/admin/orders/${order.id}`" class="btn btn-sm btn-primary">
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
    
    <!-- Recent Users -->
    <div class="card admin-card shadow-sm mt-4">
      <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
        <h5 class="mb-0">Recent Users</h5>
        <router-link to="/admin/users" class="btn btn-sm btn-primary">
          Manage Users
        </router-link>
      </div>
      <div class="card-body">
        <div v-if="loading" class="text-center py-3">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-else class="table-responsive">
          <table class="table admin-table table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Registered</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in recentUsers" :key="user.id">
                <td>{{ user.id }}</td>
                <td>{{ user.username }}</td>
                <td>{{ user.email }}</td>
                <td>
                  <span class="badge" :class="getRoleBadgeClass(user.role)">
                    {{ user.role }}
                  </span>
                </td>
                <td>{{ formatDate(user.created_at) }}</td>
                <td class="user-actions">
                  <router-link :to="`/admin/users/edit/${user.id}`" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i>
                  </router-link>
                </td>
              </tr>
              <tr v-if="recentUsers.length === 0">
                <td colspan="6" class="text-center py-3">No users found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Products -->
    <div class="card admin-card shadow-sm mt-4">
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
          <table class="table admin-table table-hover">
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
                  <router-link :to="`/admin/products/edit/${product.id}`" class="btn btn-sm btn-primary">
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
import axios from 'axios'

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
      recentUsers: [],
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
        'bg-success': status === 'completed' || status === 'delivered', 
        'bg-warning text-dark': status === 'pending',
        'bg-info text-dark': status === 'processing',
        'bg-primary': status === 'shipped',
        'bg-danger': status === 'cancelled'
      }
    },
    
    getRoleBadgeClass(role) {
      return {
        'bg-danger': role === 'admin',
        'bg-primary': role === 'user'
      }
    },
    
    async fetchDashboardData() {
      try {
        this.loading = true
        
        // Fetch orders using axios instead of fetch
        const ordersResponse = await axios.get('/api/admin/orders')
        if (ordersResponse.data.orders) {
          this.recentOrders = ordersResponse.data.orders.slice(0, 5)
          this.stats.totalOrders = ordersResponse.data.pagination.total
          
          // Calculate revenue
          this.stats.revenue = ordersResponse.data.orders.reduce((total, order) => {
            return total + Number(order.total_amount)
          }, 0)
        }
        
        // Fetch users using axios instead of fetch
        const usersResponse = await axios.get('/api/admin/users')
        if (usersResponse.data.users) {
          this.recentUsers = usersResponse.data.users.slice(0, 5)
          this.stats.totalUsers = usersResponse.data.pagination.total
        }
        
        // Fetch products using axios instead of fetch
        const productsResponse = await axios.get('/api/products')
        if (productsResponse.data.products) {
          this.products = productsResponse.data.products.slice(0, 5)
          this.stats.totalProducts = productsResponse.data.products.length
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