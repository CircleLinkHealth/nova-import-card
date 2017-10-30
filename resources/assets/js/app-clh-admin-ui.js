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


window.App = new Vue({
    el: '#app',
    store
});

