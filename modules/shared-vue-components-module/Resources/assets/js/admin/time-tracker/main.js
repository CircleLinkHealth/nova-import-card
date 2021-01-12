import Vue from 'vue'

//custom components
import TimeTracker from './index'
import EventBus from './comps/event-bus'
import { BindWindowFocusChange, BindWindowVisibilityChange } from './events/window.event'

Vue.config.productionTip = false

var TimeTrackerApp = new Vue({
  el: '#time-tracker',
  template: '<time-tracker :info="timeTrackerInfo"></time-tracker>',
  data: {
    timeTrackerInfo: window.timeTrackerInfo || {}
  },
  components: { 
    'time-tracker': TimeTracker
  },
  mounted() {
      if (Object.keys(this.timeTrackerInfo).length === 0) {
          throw new Error("Time-Tracker: Info Object should have values");
      }
  }
})

BindWindowFocusChange(window)
BindWindowVisibilityChange(window, document)