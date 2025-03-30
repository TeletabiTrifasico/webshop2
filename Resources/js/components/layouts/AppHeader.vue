<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <router-link class="navbar-brand" to="/">{{ appName }}</router-link>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <router-link class="nav-link" to="/products">Products</router-link>
          </li>
        </ul>
        
        <ul class="navbar-nav">
          <li v-if="isAuthenticated" class="nav-item">
            <router-link to="/cart" class="nav-link position-relative">
              Cart
              <span v-if="cartCount > 0" class="badge bg-primary rounded-pill cart-count">
                {{ cartCount }}
              </span>
            </router-link>
          </li>
          
          <template v-if="isAuthenticated">
            <li class="nav-item">
              <router-link class="nav-link" to="/user/profile">
                {{ user?.username }}
              </router-link>
            </li>
            <li class="nav-item">
              <a @click.prevent="handleLogout" href="#" class="nav-link">Logout</a>
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
    ...mapState('cart', ['count as cartCount']),
    
    appName() {
      return process.env.VUE_APP_NAME || 'Webshop'
    },
    
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