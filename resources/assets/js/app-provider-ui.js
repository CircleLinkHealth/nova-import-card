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

Vue.component('component-proxy', require('./components/shared/component-proxy.vue'));
Vue.component('createAppointmentsAddCarePerson', require('./components/CareTeam/create-appointments-add-care-person.vue'));
Vue.component('createCarePerson', require('./components/CareTeam/create-care-person.vue'));
Vue.component('updateCarePerson', require('./components/pages/view-care-plan/update-care-person.vue'));
Vue.component('indexCarePerson', require('./components/pages/view-care-plan/index-care-person.vue'));
Vue.component('careTeam', require('./components/pages/view-care-plan/care-team.vue'));
Vue.component('select2', require('./components/src/select2'));
Vue.component('fab', require('./components/fab.vue'));
Vue.component('openModal', require('./components/shared/open-modal.vue'));
Vue.component('notifications', require('./components/shared/notifications/notifications.vue'));
Vue.component('pdfCareplans', require('./components/pages/view-care-plan/pdf-careplans.vue'));
Vue.component('medicationsList', require('./ccd-models/medications.vue'));
Vue.component('problemsList', require('./ccd-models/problems.vue'));
Vue.component('allergiesList', require('./ccd-models/allergies.vue'));

window.App = new Vue({
    el: '#app',
    store
});

