require('./bootstrap');

window.Vue = require('vue');

var getUrl = window.location;
var baseUrl = getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];

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

Vue.component('nurseDailyHours', require('./components/pages/work-schedule/daily-hours.vue'));

window.App = new Vue({
    el: '#app',
    store
});

