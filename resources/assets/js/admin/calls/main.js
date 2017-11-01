import Vue from 'vue'
import CallMgmtApp from './app'
import VueResource from 'vue-resource'
import { ClientTable, Event } from 'vue-tables-2'

Vue.use(VueResource)
Vue.use(ClientTable, {}, false)
Vue.config.productionTip = false

const App = new Vue({
  el: '#call-mgmt-app',
  template: '<call-mgmt-app/>',
  components: { 
    'call-mgmt-app': CallMgmtApp, 
    'v-client-table': ClientTable
  }
})
