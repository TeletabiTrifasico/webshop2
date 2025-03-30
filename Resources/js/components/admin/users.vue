<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>Manage Users</h1>
      <router-link to="/admin/users/create" class="btn btn-success">
        Add New User
      </router-link>
    </div>

    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Created</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.id }}</td>
            <td>{{ user.username }}</td>
            <td>{{ user.email }}</td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td>
              <span :class="['badge', getRoleBadgeClass(user.role)]">
                {{ capitalize(user.role) }}
              </span>
            </td>
            <td>
              <router-link 
                :to="`/admin/users/edit/${user.id}`"
                class="btn btn-sm btn-primary me-2">
                Edit
              </router-link>
              <button 
                @click="deleteUser(user.id)"
                class="btn btn-sm btn-danger">
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminUsers',

  data() {
    return {
      users: []
    }
  },

  methods: {
    formatDate(date) {
      return new Date(date).toLocaleDateString()
    },

    capitalize(str) {
      return str.charAt(0).toUpperCase() + str.slice(1)
    },

    getRoleBadgeClass(role) {
      return {
        'bg-warning': role === 'admin',
        'bg-secondary': role === 'user'
      }
    },

    async fetchUsers() {
      try {
        const response = await this.$axios.get('/admin/users')
        this.users = response.data.users
      } catch (error) {
        console.error('Error fetching users:', error)
        this.$toast.error('Failed to load users')
      }
    },

    async deleteUser(id) {
      if (!confirm('Are you sure you want to delete this user?')) {
        return
      }

      try {
        const response = await this.$axios.delete(`/admin/users/${id}`)
        if (response.data.success) {
          this.$toast.success('User deleted successfully')
          await this.fetchUsers()
        }
      } catch (error) {
        console.error('Error:', error)
        this.$toast.error('Failed to delete user')
      }
    }
  },

  created() {
    this.fetchUsers()
  }
}
</script>