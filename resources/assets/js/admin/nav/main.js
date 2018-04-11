import Vue from 'vue'

//custom components
import NavApp from './app'

Vue.config.productionTip = false

const App = new Vue({
  el: '#nav-app',
  template: '<nav-app/>',
  components: { 
    'nav-app': NavApp
  }
})
