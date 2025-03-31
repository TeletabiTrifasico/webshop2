<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>Users</h1>
      <router-link to="/admin/users/create" class="btn btn-success">
        <i class="fas fa-plus me-2"></i> Add New User
      </router-link>
    </div>

    <!-- Search -->
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="mb-0">
              <label class="form-label">Search Users</label>
              <input type="text" 
                     v-model="searchQuery" 
                     class="form-control"
                     placeholder="Search by username or email...">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow-sm">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3">Loading users...</p>
        </div>
        
        <div v-else-if="error" class="alert alert-danger">
          {{ error }}
        </div>
        
        <div v-else-if="filteredUsers.length === 0" class="text-center py-5">
          <div class="mb-4">
            <i class="fas fa-users fa-4x text-muted"></i>
          </div>
          <h3>No users found</h3>
          <p class="text-muted">Try adjusting your search.</p>
        </div>
        
        <div v-else class="table-responsive">
          <table class="table table-hover">
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
              <tr v-for="user in filteredUsers" :key="user.id">
                <td>{{ user.id }}</td>
                <td>{{ user.username }}</td>
                <td>{{ user.email }}</td>
                <td>
                  <span :class="getRoleBadgeClass(user.role)">
                    {{ user.role }}
                  </span>
                </td>
                <td>{{ formatDate(user.created_at) }}</td>
                <td>
                  <div class="btn-group">
                    <router-link 
                      :to="`/admin/users/edit/${user.id}`" 
                      class="btn btn-sm btn-primary me-2">
                      <i class="fas fa-edit"></i> Edit
                    </router-link>
                    <button 
                      @click="toggleUserRole(user)"
                      class="btn btn-sm btn-outline-secondary me-2">
                      Toggle Role
                    </button>
                    <button 
                      @click="deleteUser(user.id)"
                      class="btn btn-sm btn-danger"
                      :disabled="user.id === currentUserId">
                      <i class="fas fa-trash"></i> Delete
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
import { useStore } from 'vuex'
import axios from 'axios'

export default {
  name: 'AdminUsers',

  setup() {
    const store = useStore()
    const users = ref([])
    const loading = ref(true)
    const error = ref(null)
    const searchQuery = ref('')

    const currentUser = computed(() => store.state.auth.user)
    const currentUserId = computed(() => currentUser.value?.id)

    const filteredUsers = computed(() => {
      if (!searchQuery.value) return users.value
      
      const query = searchQuery.value.toLowerCase()
      return users.value.filter(user => 
        user.username.toLowerCase().includes(query) || 
        user.email.toLowerCase().includes(query)
      )
    })

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString()
    }

    const getRoleBadgeClass = (role) => {
      return {
        'badge bg-danger': role === 'admin',
        'badge bg-primary': role === 'user'
      }
    }

    const fetchUsers = async () => {
      try {
        loading.value = true
        const response = await axios.get('/api/admin/users')
        
        if (response.data.users) {
          users.value = response.data.users
        } else {
          throw new Error('Failed to load users')
        }
      } catch (err) {
        error.value = err.message || 'An error occurred while loading users'
        console.error(err)
      } finally {
        loading.value = false
      }
    }

    const toggleUserRole = async (user) => {
      try {
        const newRole = user.role === 'admin' ? 'user' : 'admin'
        const response = await axios.put(`/api/admin/users/${user.id}/role`, {
          role: newRole
        })
        
        if (response.data.success) {
          user.role = newRole
        } else {
          throw new Error(response.data.message || 'Failed to update user role')
        }
      } catch (err) {
        alert(err.response?.data?.error || err.message || 'An error occurred while updating the user role')
        console.error(err)
      }
    }

    const deleteUser = async (id) => {
      // Don't allow deleting yourself
      if (id === currentUserId.value) {
        alert('You cannot delete your own account')
        return
      }

      if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return
      }

      try {
        loading.value = true
        const response = await axios.delete(`/api/admin/users/${id}`)
        
        if (response.data.success) {
          // Remove the user from the list
          users.value = users.value.filter(u => u.id !== id)
        } else {
          throw new Error(response.data.message || 'Failed to delete user')
        }
      } catch (err) {
        alert(err.response?.data?.error || err.message || 'An error occurred while deleting the user')
        console.error(err)
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchUsers()
    })

    return {
      users,
      filteredUsers,
      loading,
      error,
      searchQuery,
      currentUserId,
      formatDate,
      getRoleBadgeClass,
      toggleUserRole,
      deleteUser
    }
  }
}
</script>