require('./bootstrap');

window.Vue = require('vue');

window.axios.defaults.baseURL = $('meta[name="base-url"]').attr('content');

import VueForm from "vue-form";
import store from "./store";

window.Vue.config.debug = store.state.debug

window.Vue.use(VueForm, {
    inputClasses: {
        valid: 'form-control-success',
        invalid: 'form-control-danger'
    }
});

Vue.component('component-proxy', require('./components/shared/component-proxy.vue'));
Vue.component('createCarePerson', require('./components/CareTeam/create-care-person.vue'));
Vue.component('select2', require('./components/src/select2'));
Vue.component('fab', require('./components/fab.vue'));
Vue.component('openModal', require('./components/shared/open-modal.vue'));
Vue.component('notifications', require('./components/shared/notifications/notifications.vue'));


window.App = new Vue({
    el: '#v-fab',
    store
});

