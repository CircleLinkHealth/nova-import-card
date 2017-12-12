require('./bootstrap');

window.Vue = require('vue');

window.axios.defaults.baseURL = $('meta[name="base-url"]').attr('content');

Vue.prototype.axios = window.axios

import VueForm from "vue-form";
import store from "./store";
import { ClientTable } from 'vue-tables-2'

window.Vue.use(VueForm, {
    inputClasses: {
        valid: 'form-control-success',
        invalid: 'form-control-danger'
    }
});

Vue.use(ClientTable, {}, false)

Vue.component('nurseDailyHours', require('./components/pages/work-schedule/daily-hours.vue'));
Vue.component('importerTrainer', require('./components/Importer/trainer.vue'));
Vue.component('select2', require('./components/src/select2'));
Vue.component('billing-report', require('./admin/billing/index.vue'));

window.App = new Vue({
    el: '#app',
    store
});

