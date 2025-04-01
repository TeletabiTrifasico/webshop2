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
          <li class="nav-item" v-if="isAdmin">
            <router-link class="nav-link" to="/admin">Admin</router-link>
          </li>
        </ul>

        <ul class="navbar-nav align-items-center">
          <template v-if="isAuthenticated">
            <li class="nav-item me-3 position-relative">
              <router-link to="/cart" class="nav-link cart-link">
                <i class="fas fa-shopping-cart"></i>
                <span v-if="cartCount > 0" class="badge bg-danger cart-count">
                  {{ cartCount }}
                </span>
              </router-link>
            </li>
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
import { computed, onMounted, watch } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'

export default {
  name: 'AppHeader',

  setup() {
    const store = useStore()
    const router = useRouter()
    
    const user = computed(() => store.state.auth.user)
    const isAuthenticated = computed(() => !!user.value)
    const isAdmin = computed(() => isAuthenticated.value && user.value?.role === 'admin')
    const cartItems = computed(() => store.state.cart.items)
    const cartCount = computed(() => cartItems.value.length)
    
    const handleLogout = async () => {
      await store.dispatch('auth/logout')
      router.push('/auth/login')
    }
    
    const fetchCart = () => {
      if (isAuthenticated.value) {
        store.dispatch('cart/fetchCart')
      }
    }
    
    onMounted(() => {
      fetchCart()
    })
    
    watch(isAuthenticated, (newValue) => {
      if (newValue) {
        fetchCart()
      }
    })
    
    return {
      user,
      isAuthenticated,
      isAdmin,
      cartCount,
      handleLogout
    }
  }
}
</script>

<style scoped>
.cart-link {
    position: relative;
    padding-right: 8px;
}
  
.cart-count {
    position: absolute;
    top: 5px;
    right: -8px;
    min-width: 18px;
    height: 18px;
    font-size: 11px;
    border-radius: 15%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 0px;
}
</style>