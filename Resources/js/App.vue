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
import { mapState, mapActions } from 'vuex'
import AppHeader from './AppHeader.vue'
import AppFooter from './AppFooter.vue'

export default {
  name: 'App',
  
  components: {
    AppHeader,
    AppFooter
  },
  
  data() {
    return {
      appName: process.env.VUE_APP_NAME || 'Webshop'
    }
  },

  computed: {
    ...mapState('auth', ['user']),
    ...mapState('cart', ['count as cartCount']),
    
    isAuthenticated() {
      return !!this.user
    },
    
    currentYear() {
      return new Date().getFullYear()
    }
  },

  methods: {
    ...mapActions('auth', ['logout']),
    
    async handleLogout() {
      await this.logout()
      this.$router.push('/auth/login')
    }
  },

  created() {
    if (this.isAuthenticated) {
      this.$store.dispatch('cart/fetchCart')
    }
  }
}
</script>

<style lang="scss">
@import '../scss/app.scss';
</style>