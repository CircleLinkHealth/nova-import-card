import Vue from 'vue'

//custom components
import { TimeTracker } from './index'
import { BindWindowFocusChange, BindWindowVisibilityChange } from './events/window.event'

Vue.config.productionTip = false

const App = new Vue({
  el: '#time-tracker',
  template: '<time-tracker :info="info"/>',
  data: {
    info: pageInfo || {}
  },
  components: { 
    'time-tracker': TimeTracker
  },
  mounted() {
      if (Object.keys(this.info).length === 0) {
          throw new Error("Time-Tracker: Info Object should have values");
      }
  }
})

BindWindowFocusChange(window, App)
BindWindowVisibilityChange(window, document, App)