require('./bootstrap');

require('../../../public/js/materialize.min')

window.Vue = require('vue');

window.axios.defaults.baseURL = $('meta[name="base-url"]').attr('content');

if (!window.axios.defaults.baseURL) {
    console.log('Error: base url not found.')
}

import VueForm from "vue-form";
import store from "./store";

window.Vue.config.debug = store.state.debug

window.Vue.use(VueForm, {
    inputClasses: {
        valid: 'form-control-success',
        invalid: 'form-control-danger'
    }
});

Vue.component('v-input', require('./components/shared/materialize/input.vue'))

Vue.component('managePracticeLocations', require('./components/pages/provider-admin-panel/manage-practice-locations.vue'));
Vue.component('managePracticeUsers', require('./components/pages/provider-admin-panel/manage-practice-staff.vue'));
Vue.component('select2', require('./components/src/select2'));
Vue.component('openModal', require('./components/shared/open-modal.vue'));
Vue.component('notifications', require('./components/shared/notifications/notifications.vue'));
Vue.component('grid', require('./components/shared/grid.vue'));


window.App = new Vue({
    el: '#app',
    store,
    components: {

    }
});

