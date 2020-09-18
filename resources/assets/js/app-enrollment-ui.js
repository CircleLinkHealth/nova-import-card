import store from "../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/store";

require('../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/bootstrap');

import 'es6-string-polyfills';
import '../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/prototypes/array.prototype';
import Vue from 'vue';
import axios from '../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/bootstrap-axios';
import VueAxios from 'vue-axios';
import EventBus from '../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/admin/time-tracker/comps/event-bus';
import {BindWindowFocusChange, BindWindowVisibilityChange} from '../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/admin/time-tracker/events/window.event'

if (document) {
    const elem = document.querySelector('meta[name="base-url"]');
    if (elem) {
        axios.defaults.baseURL = elem.getAttribute('content');
    }
    else {
        console.error('base url not found.')
    }
}

Vue.use(VueAxios, axios);
Vue.config.debug = true;

const EnrollmentDashboard = () => import(/* webpackChunkName: "chunk-enrollment" */ './components/enrollment/dashboard');
const PatientToEnroll = () => import(/* webpackChunkName: "chunk-enrollment" */ './components/enrollment/patient-to-enroll');

Vue.component('enrollment-dashboard', EnrollmentDashboard);
Vue.component('patient-to-enroll',PatientToEnroll);

const App = new Vue({
    el: '#app'
});

App.EventBus = EventBus

if (window) {
    window.App = App;
    window.Vue = Vue;
}

BindWindowFocusChange(window)
BindWindowVisibilityChange(window, document)

