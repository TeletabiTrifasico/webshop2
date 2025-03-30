<template>
  <div class="container mt-4">
    <div class="row">
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">{{ user.name }}</h5>
            <p class="card-text">{{ user.email }}</p>
          </div>
          <div class="list-group list-group-flush">
            <router-link to="/user/profile"
                        class="list-group-item list-group-item-action active">
              Profile
            </router-link>
            <router-link to="/user/orders"
                        class="list-group-item list-group-item-action">
              Orders
            </router-link>
            <router-link to="/user/addresses"
                        class="list-group-item list-group-item-action">
              Addresses
            </router-link>
          </div>
        </div>
      </div>

      <div class="col-md-9">
        <div class="card">
          <div class="card-header">Profile Information</div>
          <div class="card-body">
            <form @submit.prevent="updateProfile">
              <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text"
                       id="name"
                       v-model="form.name"
                       class="form-control"
                       required>
              </div>

              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email"
                       id="email"
                       v-model="form.email"
                       class="form-control"
                       required>
              </div>

              <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="tel"
                       id="phone"
                       v-model="form.phone"
                       class="form-control">
              </div>

              <button type="submit"
                      class="btn btn-primary"
                      :disabled="loading">
                Update Profile
              </button>
            </form>
          </div>
        </div>

        <div class="card mt-4">
          <div class="card-header">Change Password</div>
          <div class="card-body">
            <form @submit.prevent="updatePassword">
              <div class="mb-3">
                <label for="current_password" class="form-label">
                  Current Password
                </label>
                <input type="password"
                       id="current_password"
                       v-model="passwordForm.current_password"
                       class="form-control"
                       required>
              </div>

              <div class="mb-3">
                <label for="new_password" class="form-label">
                  New Password
                </label>
                <input type="password"
                       id="new_password"
                       v-model="passwordForm.new_password"
                       class="form-control"
                       required>
              </div>

              <div class="mb-3">
                <label for="new_password_confirmation" class="form-label">
                  Confirm New Password
                </label>
                <input type="password"
                       id="new_password_confirmation"
                       v-model="passwordForm.new_password_confirmation"
                       class="form-control"
                       required>
              </div>

              <button type="submit"
                      class="btn btn-primary"
                      :disabled="passwordLoading">
                Update Password
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  name: 'UserProfile',

  data() {
    return {
      form: {
        name: '',
        email: '',
        phone: ''
      },
      passwordForm: {
        current_password: '',
        new_password: '',
        new_password_confirmation: ''
      },
      loading: false,
      passwordLoading: false
    }
  },

  computed: {
    ...mapState('auth', ['user'])
  },

  methods: {
    async updateProfile() {
      this.loading = true
      try {
        const response = await this.$axios.put('/user/profile', this.form)
        this.$store.commit('auth/setUser', response.data.user)
        this.$toast.success('Profile updated successfully')
      } catch (error) {
        console.error('Error updating profile:', error)
        this.$toast.error('Failed to update profile')
      } finally {
        this.loading = false
      }
    },

    async updatePassword() {
      this.passwordLoading = true
      try {
        await this.$axios.put('/user/password', this.passwordForm)
        this.passwordForm = {
          current_password: '',
          new_password: '',
          new_password_confirmation: ''
        }
        this.$toast.success('Password updated successfully')
      } catch (error) {
        console.error('Error updating password:', error)
        this.$toast.error('Failed to update password')
      } finally {
        this.passwordLoading = false
      }
    }
  },

  created() {
    this.form = {
      name: this.user.name,
      email: this.user.email,
      phone: this.user.phone || ''
    }
  }
}
</script>