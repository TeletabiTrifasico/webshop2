<template>
  <div class="d-flex flex-column min-vh-100">
    <app-header />
    <main class="flex-grow-1">
      <router-view />
    </main>
    <app-footer />
  </div>
</template>

<script>
import AppHeader from './components/layouts/AppHeader.vue'
import AppFooter from './components/layouts/AppFooter.vue'
import { onMounted, watch } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'App',
  
  components: {
    AppHeader,
    AppFooter
  },
  
  setup() {
    const store = useStore()
    
    // Watch for authentication changes and fetch cart data accordingly
    watch(() => store.getters['auth/isAuthenticated'], (isAuthenticated) => {
      if (isAuthenticated) {
        store.dispatch('cart/fetchCart')
      } else {
        store.commit('cart/clearCart')
      }
    })
    
    onMounted(() => {
      // Initial cart fetch if user is authenticated
      if (store.getters['auth/isAuthenticated']) {
        store.dispatch('cart/fetchCart')
      }
    })
    
    return {}
  }
}
</script>

<style lang="scss">
@import '../scss/app.scss';
</style>