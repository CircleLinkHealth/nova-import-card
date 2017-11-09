import Vue from 'vue'

//custom components
import TimeTracker from './index'
import { BindWindowFocusChange, BindWindowVisibilityChange } from './events/window.event'

Vue.config.productionTip = false

var TimeTrackerApp = new Vue({
  el: '#time-tracker',
  template: '<time-tracker :info="info"></time-tracker>',
  data: {
    info: window.pageInfo || {}
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

BindWindowFocusChange(window)
BindWindowVisibilityChange(window, document)