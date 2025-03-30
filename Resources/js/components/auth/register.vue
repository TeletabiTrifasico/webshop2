<template>
  <div class="container mt-4">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">Register</div>
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text"
                       id="name"
                       v-model="form.name"
                       class="form-control"
                       required>
              </div>

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

              <div class="mb-3">
                <label for="password_confirmation" class="form-label">
                  Confirm Password
                </label>
                <input type="password"
                       id="password_confirmation"
                       v-model="form.password_confirmation"
                       class="form-control"
                       required>
              </div>

              <button type="submit"
                      class="btn btn-primary w-100"
                      :disabled="loading">
                {{ loading ? 'Creating account...' : 'Register' }}
              </button>

              <div v-if="error" class="alert alert-danger mt-3">
                {{ error }}
              </div>
            </form>

            <div class="mt-3 text-center">
              <p>Already have an account? 
                <router-link to="/auth/login">Login</router-link>
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
  name: 'Register',

  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
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