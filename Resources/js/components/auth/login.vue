<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">Login</div>
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input 
                  type="email" 
                  class="form-control" 
                  v-model="email" 
                  required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input 
                  type="password" 
                  class="form-control" 
                  v-model="password" 
                  required>
              </div>
              <div v-if="error" class="alert alert-danger">
                {{ error }}
              </div>
              <button 
                type="submit" 
                class="btn btn-primary"
                :disabled="loading">
                {{ loading ? 'Loading...' : 'Login' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex'

export default {
  name: 'Login',
  
  data() {
    return {
      email: '',
      password: '',
      loading: false,
      error: null
    }
  },

  methods: {
    ...mapActions('auth', ['login']),

    async handleSubmit() {
      this.loading = true
      this.error = null
      
      try {
        const result = await this.login({
          email: this.email,
          password: this.password
        })

        if (result.success) {
          this.$router.push('/')
        } else {
          this.error = result.error
        }
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    }
  }
}
</script>