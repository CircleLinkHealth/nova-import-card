require('./bootstrap');

import './prototypes/array.prototype'

import Vue from 'vue'
import axios from './bootstrap-axios'
import VueAxios from 'vue-axios'
import { ClientTable } from 'vue-tables-2'

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
Vue.use(ClientTable, {}, false)

Vue.config.debug = true

import EventBus from './admin/time-tracker/comps/event-bus'
import CcdUpload from './components/Importer/ccd-upload'
import CcdViewer from './components/Importer/ccd-viewer'

Vue.component('ccd-upload', CcdUpload)
Vue.component('ccd-viewer', CcdViewer)

const App = new Vue({
    el: '#app'
})

App.EventBus = EventBus

export default App

if (window) {
    window.App = App
    window.Vue = Vue
}