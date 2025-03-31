<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">Register</div>
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="form.username" 
                  required>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input 
                  type="email" 
                  class="form-control" 
                  v-model="form.email" 
                  required>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input 
                  type="password" 
                  class="form-control" 
                  v-model="form.password" 
                  required
                  minlength="6">
              </div>

              <div v-if="error" class="alert alert-danger">
                {{ error }}
              </div>

              <button 
                type="submit" 
                class="btn btn-primary w-100"
                :disabled="loading">
                {{ loading ? 'Registering...' : 'Register' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'

export default {
  name: 'Register',
  
  data() {
    return {
      form: {
        username: '',
        email: '',
        password: ''
      }
    }
  },

  computed: {
    ...mapState('auth', ['loading', 'error'])
  },

  methods: {
    ...mapActions('auth', ['register']),

    async handleSubmit() {
      try {
        const result = await this.register(this.form)
        if (result.success) {
          this.$router.push('/')
        }
      } catch (error) {
        console.error('Registration error:', error)
      }
    }
  }
}
</script>