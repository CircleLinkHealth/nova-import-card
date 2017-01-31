var Vue = require('vue');
Vue.use(require('vue-resource'));
var vueForm = require('vue-form');
Vue.use(vueForm);
Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

require('./search-providers.js');

var carePerson = Vue.component('carePerson', {
    events: {
        'existing-user-selected': function (data) {
            this.$set('care_person.user', data.user);
        }
    },

    template: '#care-person-modal-template',

    props: [
        //This is an App\CarePerson Object with relationships User and ProviderInfo loaded.
        'care_person',
    ],

    data: function () {
        return {
            patientId: '',
            updateRoute: '',
        }
    },

    ready: function () {
        this.$set('updateRoute', $('meta[name="provider-update-route"]').attr('content'));
        this.$set('patientId', $('meta[name="patient_id"]').attr('content'));
    },

    methods: {
        updateCarePerson: function (id) {
            this.$http.patch(this.updateRoute + '/' + id, {
                careTeamMember: this.care_person,
                patientId: this.patientId,
            }).then(function (response) {
                this.$set('care_person.id', response.data.carePerson.id);
                $("#editCareTeamModal-" + id).modal('hide');
            }, function (response) {
                //error
            });
        }
    }
});

module.exports = carePerson;