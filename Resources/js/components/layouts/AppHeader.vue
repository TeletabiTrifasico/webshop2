<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <router-link class="navbar-brand" to="/">Webshop</router-link>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <router-link class="nav-link" to="/">Home</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/products">Products</router-link>
          </li>
        </ul>

        <ul class="navbar-nav align-items-center">
          <template v-if="isAuthenticated">
            <li class="nav-item me-3">
              <span class="nav-link text-light">
                <i class="fas fa-user me-1"></i>
                {{ user.username }}
              </span>
            </li>
            <li class="nav-item">
              <button @click="handleLogout" 
                      class="btn btn-outline-danger">
                <i class="fas fa-sign-out-alt me-1"></i>
                Logout
              </button>
            </li>
          </template>
          <template v-else>
            <li class="nav-item">
              <router-link class="nav-link" to="/auth/login">Login</router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/auth/register">Register</router-link>
            </li>
          </template>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script>
import { mapState, mapActions } from 'vuex'

export default {
  name: 'AppHeader',

  computed: {
    ...mapState('auth', ['user']),
    isAuthenticated() {
      return !!this.user
    }
  },

  methods: {
    ...mapActions('auth', ['logout']),
    async handleLogout() {
      await this.logout()
      this.$router.push('/auth/login')
    }
  }
}
</script>

<style scoped>
.btn-outline-danger {
  border-color: #dc3545;
  color: #dc3545;
  transition: all 0.3s ease;
}

.btn-outline-danger:hover {
  background-color: #dc3545;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
}

.nav-link {
  transition: color 0.3s ease;
}

.nav-link:hover {
  color: #fff;
}
</style>