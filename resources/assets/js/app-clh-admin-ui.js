require('./bootstrap')


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

import { ClientTable } from 'vue-tables-2'

const CallMgmtApp = () => import(/* webpackChunkName: "chunk-admin" */ './admin/calls/app')
const NurseDailyHours = () => import(/* webpackChunkName: "chunk-admin" */ './components/pages/work-schedule/daily-hours')
const ImporterTrainerComponent = () => import(/* webpackChunkName: "chunk-importer-trainer" */ './components/Importer/trainer')
const Select2Component = () => import(/* webpackChunkName: "chunk-admin" */ './components/src/select2')
const PassportClientsComponent = () => import(/* webpackChunkName: "chunk-admin" */ './components/passport/Clients')
const PassportAuthorizedClientsComponent = () => import(/* webpackChunkName: "chunk-admin" */ './components/passport/AuthorizedClients')
const PassportPersonalAccessTokensComponent = () => import(/* webpackChunkName: "chunk-admin" */ './components/passport/PersonalAccessTokens')

Vue.use(ClientTable, {}, false)

Vue.component('call-mgmt-app', CallMgmtApp)
Vue.component('nurseDailyHours', NurseDailyHours)
Vue.component('select2', Select2Component)
Vue.component('passport-clients', PassportClientsComponent)
Vue.component('passport-authorized-clients', PassportAuthorizedClientsComponent)
Vue.component('importer-trainer', ImporterTrainerComponent)
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

