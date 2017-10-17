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
Vue.component('createCarePerson', require('./components/CareTeam/create-care-person.vue'));
Vue.component('updateCarePerson', require('./components/pages/view-care-plan/update-care-person.vue'));
Vue.component('indexCarePerson', require('./components/pages/view-care-plan/index-care-person.vue'));
Vue.component('careTeam', require('./components/pages/view-care-plan/care-team.vue'));
Vue.component('select2', require('./components/src/select2'));
Vue.component('openModal', require('./components/shared/open-modal.vue'));
Vue.component('notifications', require('./components/shared/notifications/notifications.vue'));
Vue.component('pdfCareplans', require('./components/pages/view-care-plan/pdf-careplans.vue'));

window.App = new Vue({
    el: '#v-pdf-careplans',
    store
});

