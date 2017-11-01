// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import CallMgmtApp from './app'
import VueResource from 'vue-resource'
import { ClientTable, Event } from 'vue-tables-2'


Vue.use(VueResource)
Vue.use(ClientTable, {}, false)
Vue.config.productionTip = false

/* eslint-disable no-new */
const App = new Vue({
  el: '#call-mgmt-app',
  template: '<call-mgmt-app/>',
  components: { 
    'call-mgmt-app': CallMgmtApp, 
    'v-client-table': ClientTable
  }
})
