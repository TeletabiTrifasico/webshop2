<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1>{{ isEditing ? 'Edit User' : 'Add New User' }}</h1>
      <router-link to="/admin/users" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Users
      </router-link>
    </div>

    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <div class="card shadow-sm">
      <div class="card-body">
        <form @submit.prevent="saveUser">
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input 
              type="text" 
              class="form-control" 
              id="username" 
              v-model="user.username" 
              required
              :disabled="isEditing"
            >
            <small v-if="isEditing" class="text-muted">Username cannot be changed</small>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input 
              type="email" 
              class="form-control" 
              id="email" 
              v-model="user.email" 
              required
            >
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">
              {{ isEditing ? 'Password (leave blank to keep current)' : 'Password' }}
            </label>
            <input 
              type="password" 
              class="form-control" 
              id="password" 
              v-model="user.password" 
              :required="!isEditing"
            >
          </div>

          <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" v-model="user.role" required>
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>

          <div class="d-flex justify-content-end">
            <button 
              type="button" 
              class="btn btn-outline-secondary me-2" 
              @click="$router.push('/admin/users')"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="btn btn-primary" 
              :disabled="loading"
            >
              <span v-if="loading">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Saving...
              </span>
              <span v-else>Save User</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from 'vuex'
import axios from 'axios'

export default {
  name: 'UserForm',

  setup() {
    const route = useRoute()
    const router = useRouter()
    const store = useStore()
    
    const userId = computed(() => route.params.id)
    const isEditing = computed(() => !!userId.value)
    const loading = ref(false)
    const error = ref(null)
    const currentUser = computed(() => store.state.auth.user)

    const user = ref({
      username: '',
      email: '',
      password: '',
      role: 'user'
    })

    const fetchUser = async () => {
      if (!isEditing.value) return

      try {
        loading.value = true
        const response = await axios.get(`/api/admin/users/${userId.value}`)
        
        if (response.data.user) {
          // Copy user data but don't include password
          user.value = { 
            ...response.data.user,
            password: '' // Don't show password, it will only be set if entered
          }
        } else {
          throw new Error('User not found')
        }
      } catch (err) {
        error.value = 'Failed to load user'
        console.error(err)
      } finally {
        loading.value = false
      }
    }

    const saveUser = async () => {
      try {
        loading.value = true
        error.value = null
        
        // For safety, check if user is trying to edit themselves and change role
        if (isEditing.value && 
            Number(userId.value) === currentUser.value.id && 
            user.value.role !== currentUser.value.role) {
          error.value = 'You cannot change your own role'
          return
        }
        
        let response
        
        if (isEditing.value) {
          // For editing, only send the fields that should be updated
          const userData = {
            email: user.value.email,
            role: user.value.role
          }
          
          // Only include password if it was provided
          if (user.value.password) {
            userData.password = user.value.password
          }
          
          response = await axios.put(`/api/admin/users/${userId.value}`, userData)
        } else {
          // For creating
          response = await axios.post('/api/admin/users', user.value)
        }
        
        if (response.data.success) {
          router.push('/admin/users')
        } else {
          throw new Error(response.data.error || 'Failed to save user')
        }
      } catch (err) {
        error.value = err.response?.data?.error || err.message || 'An error occurred while saving the user'
        console.error(err)
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchUser()
    })

    return {
      user,
      isEditing,
      loading,
      error,
      saveUser
    }
  }
}
</script>