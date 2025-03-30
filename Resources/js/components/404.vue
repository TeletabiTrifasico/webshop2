<template>
  <div class="container text-center mt-5">
    <h1>404 - Page Not Found</h1>
    <p>The page you are looking for does not exist.</p>
    <router-link to="/" class="btn btn-primary">
      Return to Home
    </router-link>
  </div>
</template>

<script>
export default {
  name: 'NotFound'
}
</script>