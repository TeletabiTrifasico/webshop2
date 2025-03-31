<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card text-center">
          <div class="card-body py-5">
            <div class="mb-4">
              <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
            </div>
            <h1 class="card-title mb-4">Order Successful!</h1>
            <p class="card-text lead mb-1">
              Thank you for your purchase.
            </p>
            <p class="mb-4">Your order #{{ orderId }} has been placed successfully.</p>
            
            <div class="d-flex justify-content-center gap-3">
              <router-link to="/" class="btn btn-primary">
                Continue Shopping
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRoute } from 'vue-router'

export default {
  name: 'OrderSuccess',
  
  setup() {
    const route = useRoute()
    const orderId = ref(route.params.orderId)
    
    return {
      orderId
    }
  }
}
</script>