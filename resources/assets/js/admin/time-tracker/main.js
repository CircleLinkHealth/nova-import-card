import Vue from 'vue'

//custom components
import { TimeTracker } from './index'
import { BindWindowFocusChange, BindWindowVisibilityChange } from './events/window.event'

Vue.config.productionTip = false

const App = new Vue({
  el: '#time-tracker',
  template: '<time-tracker/>',
  components: { 
    'time-tracker': TimeTracker
  }
})

BindWindowFocusChange(window, App)
BindWindowVisibilityChange(window, document, App)