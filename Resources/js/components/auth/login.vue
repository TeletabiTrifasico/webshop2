<template>
  <div class="container mt-4">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">Login</div>
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" 
                       id="email"
                       v-model="form.email"
                       class="form-control"
                       required>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password"
                       id="password"
                       v-model="form.password"
                       class="form-control"
                       required>
              </div>

              <div class="mb-3 form-check">
                <input type="checkbox"
                       id="remember"
                       v-model="form.remember"
                       class="form-check-input">
                <label for="remember" class="form-check-label">
                  Remember me
                </label>
              </div>

              <button type="submit" 
                      class="btn btn-primary w-100"
                      :disabled="loading">
                {{ loading ? 'Logging in...' : 'Login' }}
              </button>

              <div v-if="error" class="alert alert-danger mt-3">
                {{ error }}
              </div>
            </form>

            <div class="mt-3 text-center">
              <p>Don't have an account? 
                <router-link to="/auth/register">Register</router-link>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'

export default {
  name: 'Login',

  data() {
    return {
      form: {
        email: '',
        password: '',
        remember: false
      }
    }
  },

  computed: {
    ...mapState('auth', ['loading', 'error'])
  },

  methods: {
    ...mapActions('auth', ['login']),

    async handleSubmit() {
      try {
        const result = await this.login(this.form)
        if (result.success) {
          const redirect = this.$route.query.redirect || '/'
          this.$router.push(redirect)
        }
      } catch (error) {
        console.error('Login error:', error)
      }
    }
  }
}
</script>