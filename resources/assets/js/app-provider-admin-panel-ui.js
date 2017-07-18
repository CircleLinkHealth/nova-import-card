require('./bootstrap');

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
Vue.component('createPracticeLocation', require('./components/practice/lodations/create.vue'));
Vue.component('updatePracticeLocation', require('./components/practice/lodations/update.vue'));
Vue.component('indexPracticeLocations', require('./components/practice/lodations/index.vue'));
Vue.component('select2', require('./components/src/select2'));
Vue.component('openModal', require('./components/shared/open-modal.vue'));
Vue.component('notifications', require('./components/shared/notifications/notifications.vue'));
Vue.component('grid', require('./components/shared/grid.vue'));

window.App = new Vue({
    el: '#app',
    store
});

