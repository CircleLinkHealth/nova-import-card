import VueForm from "vue-form";
import store from "./store";

window.Vue.config.debug = true

window.Vue.use(VueForm, {
    inputClasses: {
        valid: 'form-control-success',
        invalid: 'form-control-danger'
    }
});

Vue.component('createAppointmentsAddCarePerson', require('./components/CareTeam/create-appointments-add-care-person.vue'));

window.App = new Vue({
    el: '#v-create-appointments-add-care-person',
    store
});
