/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('typeface-poppins');

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

import Navigation from "./components/Navigation";
import VitalsSurvey from './components/VitalsSurvey';
import VitalsSurveyNotAuthorized from './components/VitalsSurveyNotAuthorized';
import VitalsSurveyWelcome from './components/VitalsSurveyWelcome';
import SurveyQuestions from './components/SurveyQuestions';
import PatientList from './components/PatientList';
import EnrollUser from './components/EnrollUser';
import SendAssessmentLink from './components/SendAssessmentLink';
import {ClientTable, ServerTable} from 'vue-tables-2';
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
import $ from 'jquery';
import moment from 'moment';
import dateRangePicker from 'daterangepicker';
import 'lodash';
import 'popper.js';

Vue.component('navigation', Navigation);
Vue.component('survey-questions', SurveyQuestions);
Vue.component('vitals-survey', VitalsSurvey);
Vue.component('vitals-survey-not-authorized', VitalsSurveyNotAuthorized);
Vue.component('vitals-survey-welcome', VitalsSurveyWelcome);
Vue.component('patient-list', PatientList);
Vue.component('enroll-user', EnrollUser);
Vue.component('send-assessment-link', SendAssessmentLink);

Vue.use(ClientTable, {}, false);
Vue.use(ServerTable, {}, false, 'bootstrap4');

$().daterangepicker = dateRangePicker;
window.$ = $;
window.moment = moment;

require('jquery.scrollto');

export const app = new Vue({
    el: '#app',
    created() {
    }
});
