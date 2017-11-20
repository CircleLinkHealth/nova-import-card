require('./bootstrap');

import Vue from 'vue'
import axios from './bootstrap-axios'
import VueAxios from 'vue-axios'

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

import CcdUpload from './components/importer/ccd-upload'

Vue.component('ccd-upload', CcdUpload)

const App = new Vue({
    el: '#app'
})

export default App

if (window) {
    window.App = App
    window.Vue = Vue
}