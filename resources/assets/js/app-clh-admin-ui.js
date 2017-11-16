require('./bootstrap');

window.Vue = require('vue');

window.axios.defaults.baseURL = $('meta[name="base-url"]').attr('content');

import VueForm from "vue-form";
import store from "./store";

window.Vue.use(VueForm, {
    inputClasses: {
        valid: 'form-control-success',
        invalid: 'form-control-danger'
    }
});

Vue.component('nurseDailyHours', require('./components/pages/work-schedule/daily-hours.vue'));
Vue.component('importerTrainer', require('./components/Importer/trainer.vue'));
Vue.component('select2', require('./components/src/select2'));
Vue.component('passport-clients', require('./components/passport/Clients.vue'));
Vue.component('passport-authorized-clients', require('./components/passport/AuthorizedClients.vue'));
Vue.component('passport-personal-access-tokens', require('./components/passport/PersonalAccessTokens.vue'));

window.App = new Vue({
    el: '#app',
    store
});

