import store from '../store'

export function authGuard(to, from, next) {
  const isAuthenticated = store.state.auth.user !== null
  
  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }
  
  if (to.meta.requiresGuest && isAuthenticated) {
    next({ name: 'home' })
    return
  }
  
  next()
}