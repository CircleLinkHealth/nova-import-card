import store from "./store";

require('./bootstrap');

import 'es6-string-polyfills';
import './prototypes/array.prototype';
import Vue from 'vue';
import axios from './bootstrap-axios';
import VueAxios from 'vue-axios';

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
const SuggestedFamily = () => import(/* webpackChunkName: "chunk-enrollment" */ './components/enrollment/components/suggested-family');

Vue.component('enrollment-dashboard', EnrollmentDashboard);
Vue.component('suggested-family', SuggestedFamily);


const App = new Vue({
    el: '#app'
});

if (window) {
    window.Vue = Vue;
}

