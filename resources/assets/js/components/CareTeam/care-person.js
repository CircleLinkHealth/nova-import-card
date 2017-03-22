var Vue = require('vue');
Vue.use(require('vue-resource'));
var vueForm = require('vue-form');
Vue.use(vueForm);
Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

//Load components
require('./search-providers.js');

var carePerson = Vue.component('carePerson', {
    events: {
        'existing-user-selected': function (data) {
            this.$set('care_person.user', data.user);
            this.selectSpecialty(data.user.provider_info.specialty);
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
        this.selectSpecialty(this.care_person.user.provider_info.specialty);
    },

    methods: {
        //programmatically select specialty in select box
        selectSpecialty: function (specialty) {
            let selectId = '#specialty-' + this.care_person.id;
            //Update select box
            $(selectId).val(specialty).trigger("change");
        },

        updateCarePerson: function (id) {

            if (this.care_person.is_billing_provider) {
                this.$set('care_person.formatted_type', 'Billing Provider');
            }

            this.$http.patch(this.updateRoute + '/' + id, {
                careTeamMember: this.care_person,
                patientId: this.patientId,
            }).then(function (response) {
                this.$set('care_person.id', response.data.carePerson.id);
                this.$set('care_person.formatted_type', response.data.carePerson.type);
                $("#editCareTeamModal-" + id).modal('hide');

                if (response.data.oldBillingProvider) {
                    this.$dispatch('billing-provider-changed', {
                        oldBillingProvider: response.data.oldBillingProvider,
                    });
                }

                $("#successModal-" + id).modal();

                //HACK to replace select2 with newly added provider on appointments page
                let carePerson = response.data.carePerson;

                $('#providerBox').replaceWith("" +
                    "<select id='provider' " +
                    "name='provider' " +
                    "class='provider selectpickerX dropdownValid form-control' " +
                    "data-size='10' disabled>  " +
                    "<option value=" + carePerson.member_user_id + ">" + carePerson.user.first_name + ' ' + carePerson.user.last_name + "</option></select>");

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