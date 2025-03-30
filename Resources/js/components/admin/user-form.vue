<template>
  <div class="container mt-4">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">{{ isEditing ? 'Edit User' : 'Add New User' }}</h2>
          </div>
          
          <div class="card-body">
            <div v-if="error" class="alert alert-danger">
              {{ error }}
            </div>

            <form @submit.prevent="handleSubmit">
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" 
                       class="form-control" 
                       id="username" 
                       v-model="form.username"
                       required>
              </div>

              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" 
                       class="form-control" 
                       id="email" 
                       v-model="form.email"
                       required>
              </div>

              <div v-if="!isEditing" class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       v-model="form.password"
                       required>
              </div>

              <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" 
                        id="role" 
                        v-model="form.role">
                  <option value="user">User</option>
                  <option value="admin">Admin</option>
                </select>
              </div>

              <div class="d-flex justify-content-between">
                <router-link to="/admin/users" 
                            class="btn btn-secondary">
                  Cancel
                </router-link>
                <button type="submit" 
                        class="btn btn-primary"
                        :disabled="loading">
                  {{ loading ? 'Saving...' : (isEditing ? 'Update User' : 'Add User') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserForm',

  data() {
    return {
      form: {
        username: '',
        email: '',
        password: '',
        role: 'user'
      },
      loading: false,
      error: null
    }
  },

  computed: {
    isEditing() {
      return !!this.$route.params.id
    }
  },

  methods: {
    async handleSubmit() {
      this.loading = true
      this.error = null

      try {
        const url = this.isEditing 
          ? `/admin/users/${this.$route.params.id}`
          : '/admin/users'
        
        const response = await this.$axios.post(url, this.form)

        this.$toast.success(
          this.isEditing ? 'User updated successfully' : 'User created successfully'
        )
        this.$router.push('/admin/users')
      } catch (error) {
        console.error('Error:', error)
        this.error = error.response?.data?.message || 'An error occurred'
      } finally {
        this.loading = false
      }
    },

    async fetchUser() {
      try {
        const response = await this.$axios.get(`/admin/users/${this.$route.params.id}`)
        const user = response.data.user
        this.form = {
          username: user.username,
          email: user.email,
          role: user.role
        }
      } catch (error) {
        console.error('Error fetching user:', error)
        this.error = 'Failed to load user'
      }
    }
  },

  created() {
    if (this.isEditing) {
      this.fetchUser()
    }
  }
}
</script>