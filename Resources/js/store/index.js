import { createStore } from 'vuex'
import auth from './modules/auth'
import cart from './modules/cart'

export default createStore({
  modules: {
    auth,
    cart
  }
})