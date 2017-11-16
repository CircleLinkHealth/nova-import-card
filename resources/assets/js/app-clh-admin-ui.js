require('./bootstrap');

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

import CallMgmtApp from './admin/calls/app'
import { ClientTable } from 'vue-tables-2'
import NurseDailyHours from './components/pages/work-schedule/daily-hours'
import ImporterTraining from './components/Importer/trainer'
import Select2Component from './components/src/select2'
import PassportClientsComponent from './components/passport/Clients'
import PassportAuthorizedClientsComponent from './components/passport/AuthorizedClients'
import PassportPersonalAccessTokensComponent from './components/passport/PersonalAccessTokens'

Vue.use(ClientTable, {}, false)

Vue.component('call-mgmt-app', CallMgmtApp)
Vue.component('nurseDailyHours', NurseDailyHours)
Vue.component('importerTrainer', ImporterTraining)
Vue.component('select2', Select2Component)
Vue.component('passport-clients', PassportClientsComponent)
Vue.component('passport-authorized-clients', PassportAuthorizedClientsComponent)
Vue.component('passport-personal-access-tokens', PassportPersonalAccessTokensComponent)

const App = new Vue({
    el: '#app',
    store
})

export default App

if (window) {
    window.App = App
    window.Vue = Vue
}

