<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>Products</h1>
      <router-link to="/admin/products/create" class="btn btn-success">
        Add New Product
      </router-link>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <div class="mb-3">
              <label class="form-label">Category</label>
              <select v-model="filters.category" class="form-select">
                <option value="">All Categories</option>
                <option v-for="category in categories" 
                        :key="category.id"
                        :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="mb-3">
              <label class="form-label">Stock Status</label>
              <select v-model="filters.stock" class="form-select">
                <option value="">All</option>
                <option value="in_stock">In Stock</option>
                <option value="low_stock">Low Stock</option>
                <option value="out_of_stock">Out of Stock</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Search</label>
              <input type="text" 
                     v-model="filters.search" 
                     class="form-control"
                     placeholder="Search products...">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in products" :key="product.id">
                <td>
                  <img :src="product.image" 
                       :alt="product.name"
                       class="product-thumbnail">
                </td>
                <td>{{ product.name }}</td>
                <td>{{ product.category.name }}</td>
                <td>${{ formatPrice(product.price) }}</td>
                <td>{{ product.stock }}</td>
                <td>
                  <span :class="getStockStatusClass(product)">
                    {{ getStockStatusText(product) }}
                  </span>
                </td>
                <td>
                  <div class="btn-group">
                    <router-link 
                      :to="`/admin/products/${product.id}/edit`"
                      class="btn btn-sm btn-primary">
                      Edit
                    </router-link>
                    <button 
                      @click="deleteProduct(product.id)"
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
  name: 'AdminProducts',

  data() {
    return {
      products: [],
      categories: [],
      filters: {
        category: '',
        stock: '',
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

    getStockStatusClass(product) {
      if (product.stock <= 0) {
        return 'badge bg-danger'
      }
      if (product.stock <= product.min_stock) {
        return 'badge bg-warning'
      }
      return 'badge bg-success'
    },

    getStockStatusText(product) {
      if (product.stock <= 0) {
        return 'Out of Stock'
      }
      if (product.stock <= product.min_stock) {
        return 'Low Stock'
      }
      return 'In Stock'
    },

    deleteProduct(id) {
      if (confirm('Are you sure you want to delete this product?')) {
        // Implement delete logic here
      }
    },

    changePage(page) {
      this.pagination.currentPage = page
      // Implement page change logic here
    }
  }
}
</script>

<style>
.product-thumbnail {
  width: 50px;
  height: 50px;
  object-fit: cover;
}
</style>