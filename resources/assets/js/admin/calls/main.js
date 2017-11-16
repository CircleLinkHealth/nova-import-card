require('../../bootstrap');

import Vue from 'vue'
import VueResource from 'vue-resource'
import { ClientTable } from 'vue-tables-2'

//custom components
import CallMgmtApp from './app'

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
