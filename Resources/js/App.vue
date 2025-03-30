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
import AppHeader from './components/layouts/AppHeader.vue'
import AppFooter from './components/layouts/AppFooter.vue'

export default {
  name: 'App',
  
  components: {
    AppHeader,
    AppFooter
  },
  
  computed: {
    ...mapState('auth', ['user']),
    ...mapState('cart', ['count as cartCount'])
  }
}
</script>

<style lang="scss">
@import '../scss/app.scss';
</style>