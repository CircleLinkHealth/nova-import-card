require('./bootstrap');

window.Vue = require('vue');

window.axios.defaults.baseURL = $('meta[name="base-url"]').attr('content');

import VueForm from "vue-form";
import store from "./store";

window.Vue.config.debug = true

window.Vue.use(VueForm, {
    inputClasses: {
        valid: 'form-control-success',
        invalid: 'form-control-danger'
    }
});

Vue.component('component-proxy', require('./components/shared/component-proxy.vue'));
Vue.component('select2', require('./components/src/select2'));
Vue.component('fab', require('./components/fab.vue'));
Vue.component('openModal', require('./components/shared/open-modal.vue'));
Vue.component('notifications', require('./components/shared/notifications/notifications.vue'));
Vue.component('nurseDailyHours', require('./components/pages/work-schedule/daily-hours.vue'));

window.App = new Vue({
    el: '#v-show-nurse-work-schedule',
    store
});

