require('../../../../SharedVueComponents/Resources/assets/js/bootstrap')
require('hammerjs')
require('materialize-css')
require('materialize-css/js/toasts')

import Vue from 'vue'
import axios from '../../../../SharedVueComponents/Resources/assets/js/bootstrap-axios'
import VueAxios from 'vue-axios'
import VueForm from "vue-form";
import store from "../../../../SharedVueComponents/Resources/assets/js/store";

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

import InputComponent from '../../../../SharedVueComponents/Resources/assets/js/components/shared/materialize/input'
import ManagePracticeLocations from '../../../../SharedVueComponents/Resources/assets/js/components/pages/provider-admin-panel/manage-practice-locations'
import ManagePracticeUsers from '../../../../SharedVueComponents/Resources/assets/js/components/pages/provider-admin-panel/manage-practice-staff'
import Select2Component from '../../../../SharedVueComponents/Resources/assets/js/components/src/select2'
import OpenModal from '../../../../SharedVueComponents/Resources/assets/js/components/shared/open-modal'
import NotificationsComponent from '../../../../SharedVueComponents/Resources/assets/js/components/shared/notifications/notifications'
import GridComponent from '../../../../SharedVueComponents/Resources/assets/js/components/shared/grid'

Vue.component('v-input', InputComponent)
Vue.component('managePracticeLocations', ManagePracticeLocations)
Vue.component('managePracticeUsers', ManagePracticeUsers)
Vue.component('select2', Select2Component);
Vue.component('openModal', OpenModal);
Vue.component('notifications', NotificationsComponent);
Vue.component('grid', GridComponent);

const App = new Vue({
    el: '#app',
    store
})

export default App

if (window) {
    window.App = App
    window.Vue = Vue
    window.Vel = require('velocity-animate')
}