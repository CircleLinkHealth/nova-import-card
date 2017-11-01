// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import CallMgmtApp from './app'

Vue.config.productionTip = false

/* eslint-disable no-new */
const App = new Vue({
  el: '#call-mgmt-app',
  template: '<CallMgmtApp/>',
  components: { CallMgmtApp }
})
