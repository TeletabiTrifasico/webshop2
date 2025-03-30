<template>
  <div class="container mt-4">
    <h1 class="mb-4">Orders</h1>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select v-model="filters.status" class="form-select">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="mb-3">
              <label class="form-label">Date Range</label>
              <input type="date" v-model="filters.startDate" class="form-control">
            </div>
          </div>
          <div class="col-md-3">
            <div class="mb-3">
              <label class="form-label">&nbsp;</label>
              <input type="date" v-model="filters.endDate" class="form-control">
            </div>
          </div>
          <div class="col-md-3">
            <div class="mb-3">
              <label class="form-label">Search</label>
              <input 
                type="text" 
                v-model="filters.search" 
                class="form-control"
                placeholder="Order ID or Customer">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
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
              <tr v-for="order in orders" :key="order.id">
                <td>
                  <router-link :to="`/admin/orders/${order.id}`">
                    #{{ order.id }}
                  </router-link>
                </td>
                <td>{{ order.customer_name }}</td>
                <td>{{ formatDate(order.created_at) }}</td>
                <td>${{ formatPrice(order.total) }}</td>
                <td>
                  <span :class="getStatusBadgeClass(order.status)">
                    {{ order.status }}
                  </span>
                </td>
                <td>
                  <div class="btn-group">
                    <router-link 
                      :to="`/admin/orders/${order.id}`"
                      class="btn btn-sm btn-primary">
                      View
                    </router-link>
                    <button 
                      @click="deleteOrder(order.id)"
                      class="btn btn-sm btn-danger">
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <nav v-if="pagination.lastPage > 1" class="mt-4">
          <ul class="pagination justify-content-center">
            <li :class="['page-item', { disabled: pagination.currentPage === 1 }]">
              <a class="page-link" href="#" @click.prevent="changePage(1)">
                First
              </a>
            </li>
            <li :class="['page-item', { disabled: pagination.currentPage === 1 }]">
              <a class="page-link" href="#" 
                 @click.prevent="changePage(pagination.currentPage - 1)">
                Previous
              </a>
            </li>
            <li v-for="page in displayedPages" 
                :key="page"
                :class="['page-item', { active: page === pagination.currentPage }]">
              <a class="page-link" href="#" @click.prevent="changePage(page)">
                {{ page }}
              </a>
            </li>
            <li :class="['page-item', 
                        { disabled: pagination.currentPage === pagination.lastPage }]">
              <a class="page-link" href="#" 
                 @click.prevent="changePage(pagination.currentPage + 1)">
                Next
              </a>
            </li>
            <li :class="['page-item', 
                        { disabled: pagination.currentPage === pagination.lastPage }]">
              <a class="page-link" href="#" 
                 @click.prevent="changePage(pagination.lastPage)">
                Last
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminOrders',

  data() {
    return {
      orders: [],
      filters: {
        status: '',
        startDate: '',
        endDate: '',
        search: ''
      },
      pagination: {
        currentPage: 1,
        lastPage: 1,
        perPage: 15
      }
    }
  },

  computed: {
    displayedPages() {
      const pages = []
      const current = this.pagination.currentPage
      const last = this.pagination.lastPage

      if (last <= 7) {
        for (let i = 1; i <= last; i++) {
          pages.push(i)
        }
      } else {
        if (current <= 4) {
          for (let i = 1; i <= 5; i++) {
            pages.push(i)
          }
          pages.push('...')
          pages.push(last)
        } else if (current >= last - 3) {
          pages.push(1)
          pages.push('...')
          for (let i = last - 4; i <= last; i++) {
            pages.push(i)
          }
        } else {
          pages.push(1)
          pages.push('...')
          for (let i = current - 1; i <= current + 1; i++) {
            pages.push(i)
          }
          pages.push('...')
          pages.push(last)
        }
      }
      return pages
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

    async fetchOrders() {
      try {
        const params = {
          page: this.pagination.currentPage,
          ...this.filters
        }
        const response = await this.$axios.get('/admin/orders', { params })
        this.orders = response.data.orders
        this.pagination = response.data.pagination
      } catch (error) {
        console.error('Error fetching orders:', error)
        this.$toast.error('Failed to load orders')
      }
    },

    async deleteOrder(id) {
      if (!confirm('Are you sure you want to delete this order?')) {
        return
      }

      try {
        await this.$axios.delete(`/admin/orders/${id}`)
        this.$toast.success('Order deleted successfully')
        await this.fetchOrders()
      } catch (error) {
        console.error('Error deleting order:', error)
        this.$toast.error('Failed to delete order')
      }
    },

    changePage(page) {
      if (page === '...') return
      this.pagination.currentPage = page
      this.fetchOrders()
    }
  },

  watch: {
    filters: {
      deep: true,
      handler() {
        this.pagination.currentPage = 1
        this.fetchOrders()
      }
    }
  },

  created() {
    this.fetchOrders()
  }
}
</script>