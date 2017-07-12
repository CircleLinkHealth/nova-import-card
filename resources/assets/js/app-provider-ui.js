require('./bootstrap');

window.Vue = require('vue');
window.Vue.config.debug = true

import store from './store/index'

Vue.component('createCarePerson', require('./components/CareTeam/create-care-person.vue'));
Vue.component('providerUi', require('./components/provider-ui.vue'));

var getUrl = window.location;
var baseUrl = getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];

window.axios.defaults.baseURL = $('meta[name="base-url"]').attr('content');

window.App = new Vue({
    el: '#app',

    store,

    mounted() {

    },

    created () {

    }
});

