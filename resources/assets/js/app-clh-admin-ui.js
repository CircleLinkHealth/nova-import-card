require('./bootstrap')


import 'es6-string-polyfills'
import './prototypes/array.prototype'
import Vue from 'vue'
import axios from './bootstrap-axios'
import VueAxios from 'vue-axios'
import VueForm from "vue-form";
import store from "./store";

if (document) {
    const elem = document.querySelector('meta[name="base-url"]')
    if (elem) {
        axios.defaults.baseURL = elem.getAttribute('content');
    }
    else {
        console.error('base url not found.')
    }
}

Vue.use(VueAxios, axios)

Vue.config.debug = true

Vue.use(VueForm, {
    inputClasses: {
        valid: 'form-control-success',
        invalid: 'form-control-danger'
    }
});

import EventBus from './admin/time-tracker/comps/event-bus'
import { ClientTable, ServerTable } from 'vue-tables-2'

const DatepickerComponent = () => import(/* webpackChunkName: "chunk-datepicker" */ 'vuejs-datepicker')
const CallMgmtAppV2 = () => import(/* webpackChunkName: "chunk-admin" */ './admin/calls/app-v2')
const NurseDailyReport = () => import(/* webpackChunkName: "chunk-admin" */ './admin/nurse/nurse-daily-report')
const CaDirectorPanel = () => import(/* webpackChunkName: "chunk-admin" */ './admin/ca-director/panel')
const EnrolleeList = () => import(/* webpackChunkName: "chunk-admin" */ './admin/enrollment-kpis/enrollee-list')
const PracticeKPIs = () => import(/* webpackChunkName: "chunk-admin" */ './admin/enrollment-kpis/practice-kpis')
const CareAmbassadorKPIs = () => import(/* webpackChunkName: "chunk-admin" */ './admin/enrollment-kpis/careambassador-kpis')
const NurseDailyHours = () => import(/* webpackChunkName: "chunk-admin" */ './components/pages/work-schedule/daily-hours')
const ImporterTrainerComponent = () => import(/* webpackChunkName: "chunk-importer-trainer" */ './components/importer/trainer')
const Select2Component = () => import(/* webpackChunkName: "chunk-admin" */ './components/src/select2')
const TimeTrackerEventsComponent = () => import(/* webpackChunkName: "chunk-time-tracker-events" */ './admin/time-tracker/time-tracker-events')
const PassportClientsComponent = () => import(/* webpackChunkName: "chunk-admin" */ './components/passport/Clients')
const PassportAuthorizedClientsComponent = () => import(/* webpackChunkName: "chunk-admin" */ './components/passport/AuthorizedClients')
const PassportPersonalAccessTokensComponent = () => import(/* webpackChunkName: "chunk-admin" */ './components/passport/PersonalAccessTokens')
const CpmMedicationGroupsMapsSettings = () => import(/* webpackChunkName: "chunk-admin" */ './admin/cpm-medication-groups-maps-settings')
const NotificationsComponent = () => import(/* webpackChunkName: "chunk-admin" */ './components/shared/notifications/notifications')
const LoaderComponent = () => import(/* webpackChunkName: "chunk-admin" */ './components/loader')
const NurseScheduleCalendar = () => import(/* webpackChunkName: "chunk-admin" */ './admin/NursesWorkSchedules/NurseScheduleCalendar');
const CalendarLoader = () => import(/* webpackChunkName: "chunk-admin" */ './admin/NursesWorkSchedules/FullScreenLoader');
const CalendarDailyReport = () => import(/* webpackChunkName: "chunk-admin" */ './admin/NursesWorkSchedules/CalendarDailyReport');


Vue.use(ClientTable, {}, false)
Vue.use(ServerTable, {}, false)

Vue.component('nurse-daily-report', NurseDailyReport)
Vue.component('v-datepicker', DatepickerComponent)
Vue.component('call-mgmt-app', CallMgmtApp)
Vue.component('call-mgmt-app-v2', CallMgmtAppV2)
Vue.component('ca-director-panel', CaDirectorPanel)
Vue.component('enrollee-list', EnrolleeList)
Vue.component('practice-kpis', PracticeKPIs)
Vue.component('careambassador-kpis', CareAmbassadorKPIs)
Vue.component('nurseDailyHours', NurseDailyHours)
Vue.component('select2', Select2Component)
Vue.component('time-tracker-events', TimeTrackerEventsComponent)
Vue.component('passport-clients', PassportClientsComponent)
Vue.component('passport-authorized-clients', PassportAuthorizedClientsComponent)
Vue.component('importer-trainer', ImporterTrainerComponent)
Vue.component('passport-personal-access-tokens', PassportPersonalAccessTokensComponent)
Vue.component('cpm-medication-groups-maps-settings', CpmMedicationGroupsMapsSettings)
Vue.component('notifications', NotificationsComponent);
Vue.component('loader', LoaderComponent);
Vue.component('nurse-schedule-calendar', NurseScheduleCalendar);
Vue.component('calendar-loader', CalendarLoader);
Vue.component('calendar-daily-report', CalendarDailyReport);




const App = new Vue({
    el: '#app',
    store
})

App.EventBus = EventBus

export default App

if (window) {
    window.App = App
    window.Vue = Vue
}

