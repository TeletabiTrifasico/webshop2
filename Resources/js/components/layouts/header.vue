<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <router-link class="navbar-brand" to="/">{{ appName }}</router-link>
      
      <button class="navbar-toggler" 
              type="button" 
              data-bs-toggle="collapse" 
              data-bs-target="#navbarNav">
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
          <li v-if="isAdmin" class="nav-item">
            <router-link class="nav-link text-warning" to="/admin/dashboard">
              Admin Dashboard
            </router-link>
          </li>
        </ul>
        
        <ul class="navbar-nav">
          <template v-if="isAuthenticated">
            <li class="nav-item">
              <router-link class="nav-link" to="/user/profile">
                {{ user.username }}
              </router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/cart">
                Cart
                <span v-if="cartCount > 0" 
                      class="badge bg-danger rounded-pill cart-count">
                  {{ cartCount }}
                </span>
              </router-link>
            </li>
            <li class="nav-item">
              <a href="#" 
                 @click.prevent="handleLogout" 
                 class="nav-link btn btn-danger btn-sm text-white ms-2">
                Logout
              </a>
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
  name: 'Header',
  
  computed: {
    ...mapState('auth', ['user']),
    ...mapState('cart', { cartCount: 'count' }),
    
    appName() {
      return process.env.VUE_APP_NAME || 'Webshop'
    },
    
    isAuthenticated() {
      return !!this.user
    },
    
    isAdmin() {
      return this.user?.role === 'admin'
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
.cart-count {
  position: absolute;
  top: -8px;
  right: -8px;
  min-width: 18px;
  height: 18px;
  font-size: 11px;
  padding: 2px 4px;
}
</style>