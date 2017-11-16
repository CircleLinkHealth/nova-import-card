import Vue from 'vue'
import axios from '../../bootstrap-axios'
import VueAxios from 'vue-axios'
import CallMgmtApp from './app'
import { ClientTable } from 'vue-tables-2'

Vue.use(VueAxios, axios)
Vue.use(ClientTable, {}, false)
Vue.config.productionTip = false

export const App = new Vue({
  el: '#call-mgmt-app',
  template: '<call-mgmt-app/>',
  components: { 
    'call-mgmt-app': CallMgmtApp, 
    'v-client-table': ClientTable
  }
})
