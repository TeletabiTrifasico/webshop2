<template>
  <footer class="mt-auto bg-dark text-light py-4">
    <div class="container text-center">
      <p class="mb-0">&copy; {{ currentYear }} {{ appName }}. All rights reserved.</p>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'Footer',
  
  computed: {
    appName() {
      return process.env.VUE_APP_NAME || 'Webshop'
    },
    
    currentYear() {
      return new Date().getFullYear()
    }
  }
}
</script>