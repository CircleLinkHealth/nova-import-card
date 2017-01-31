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
            addCarePersonForm: {},
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
                $("#successModal-" + id).modal();

                let carePerson = response.data.carePerson;

                //setting up the select2 and dynamic picking wasn't working,
                //quick work around to replace the whole innerhtml with a
                //disabled div

                $('#providerBox').replaceWith("" +
                    "<select id='provider' " +
                    "name='provider' " +
                    "class='provider selectpickerX dropdownValid form-control' " +
                    "data-size='10' disabled>  " +
                    "<option value=" + carePerson.user_id + ">" + carePerson.user.first_name + ' ' + carePerson.user.last_name + "</option>");

                $('#providerDiv').css('padding-bottom', '10px');
                $("#save").append('<input type="hidden" value="' + carePerson.user_id + '" id="provider" name="provider">');
                $("#addProviderModal").modal('hide');

                $("#newProviderName").text(carePerson.name);

            }, function (response) {
                //error
            });
        }
    }
});

module.exports = carePerson;