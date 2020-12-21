require('../../../../Sharedvuecomponents/Resources/assets/js/bootstrap')

import 'es6-string-polyfills'
import '../../../../Sharedvuecomponents/Resources/assets/js/prototypes/array.prototype'
import Vue from 'vue'
import axios from '../../../../Sharedvuecomponents/Resources/assets/js/bootstrap-axios'
import VueAxios from 'vue-axios'
import VueForm from "vue-form";
import store from "../../../../Sharedvuecomponents/Resources/assets/js/store";

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

import EventBus from '../../../../Sharedvuecomponents/Resources/assets/js/admin/time-tracker/comps/event-bus'
import { ClientTable, ServerTable } from 'vue-tables-2'

const DatepickerComponent = () => import(/* webpackChunkName: "chunk-datepicker" */ 'vuejs-datepicker')
const CallMgmtAppV2 = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/admin/calls/app-v2')
const NurseDailyReport = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/admin/nurse/nurse-daily-report')
const CaDirectorPanel = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/admin/ca-director/panel')
const EnrolleeList = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/admin/enrollment-kpis/enrollee-list')
const PracticeKPIs = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/admin/enrollment-kpis/practice-kpis')
const CareAmbassadorKPIs = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/admin/enrollment-kpis/careambassador-kpis')
const PassportClients = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/components/passport/Clients')
const PassportAuthorizedClients = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/components/passport/AuthorizedClients')
const PassportPersonalAccessTokens = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/components/passport/PersonalAccessTokens')
const NurseDailyHours = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/components/pages/work-schedule/daily-hours')
const Select2Component = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/components/src/select2')
const TimeTrackerEventsComponent = () => import(/* webpackChunkName: "chunk-time-tracker-events" */ '../../../../Sharedvuecomponents/Resources/assets/js/admin/time-tracker/time-tracker-events')
const CpmMedicationGroupsMapsSettings = () => import(/* webpackChunkName: "chunk-admin" */ './admin/cpm-medication-groups-maps-settings')
const NotificationsComponent = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/components/shared/notifications/notifications')
const LoaderComponent = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/components/loader')
const NurseScheduleCalendar = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/admin/NursesWorkSchedules/NurseScheduleCalendar');
const CalendarLoader = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/admin/NursesWorkSchedules/FullScreenLoader');
const CalendarDailyReport = () => import(/* webpackChunkName: "chunk-admin" */ '../../../../Sharedvuecomponents/Resources/assets/js/admin/NursesWorkSchedules/CalendarDailyReport');
const UserAccountSettings = () => import(/* webpackChunkName: "chunk-user-account-settings" */ '../../../../Twofa/Resources/assets/js/user-account-settings');
const AuthyPerform2FA = () => import(/* webpackChunkName: "chunk-authy-perform-2fa" */ '../../../../Twofa/Resources/assets/js/authy-perform-2fa');



Vue.use(ClientTable, {}, false)
Vue.use(ServerTable, {}, false)

Vue.component('nurse-daily-report', NurseDailyReport)
Vue.component('v-datepicker', DatepickerComponent)
Vue.component('call-mgmt-app-v2', CallMgmtAppV2)
Vue.component('ca-director-panel', CaDirectorPanel)
Vue.component('enrollee-list', EnrolleeList)
Vue.component('practice-kpis', PracticeKPIs)
Vue.component('careambassador-kpis', CareAmbassadorKPIs)
Vue.component('nurseDailyHours', NurseDailyHours)
Vue.component('select2', Select2Component)
Vue.component('time-tracker-events', TimeTrackerEventsComponent)
Vue.component('cpm-medication-groups-maps-settings', CpmMedicationGroupsMapsSettings)
Vue.component('notifications', NotificationsComponent);
Vue.component('loader', LoaderComponent);
Vue.component('nurse-schedule-calendar', NurseScheduleCalendar);
Vue.component('calendar-loader', CalendarLoader);
Vue.component('calendar-daily-report', CalendarDailyReport);
Vue.component('user-account-settings', UserAccountSettings);
Vue.component('authy-perform-2fa', AuthyPerform2FA);
Vue.component('passport-clients', PassportClients);
Vue.component('passport-authorized-clients', PassportAuthorizedClients);
Vue.component('passport-personal-access-tokens', PassportPersonalAccessTokens);




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

