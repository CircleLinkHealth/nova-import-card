var Vue = require('vue');
Vue.use(require('vue-resource'));
Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

var carePerson = Vue.component('carePerson', {
    events: {
        'existing-user-selected': function (data) {
            alert(data.user.id);

            this.$set('care_person.user.first_name', data.user.first_name);
            this.$set('care_person.user.address', data.user.address);
        }
    },

    template: '#care-person-template',

    props: [
        //This is an App\CarePerson Object with relationships User and ProviderInfo loaded.
        'care_person',
    ],

    data: function () {
        return {
            destroyRoute: '',
            patientId: '',
            updateRoute: '',
        }
    },

    ready: function () {
        this.$set('destroyRoute', $('meta[name="provider-destroy-route"]').attr('content'));
        this.$set('updateRoute', $('meta[name="provider-update-route"]').attr('content'));
        this.$set('patientId', $('meta[name="popup_patient_id"]').attr('content'));
    },

    methods: {
        deleteCareTeamMember: function (id) {
            let disassociate = confirm('Are you sure you want to remove ' + this.care_person.user.first_name
                + ' '
                + this.care_person.user.last_name + ' from the CareTeam?');

            if (!disassociate) {
                return true;
            }

            this.$http.delete(this.destroyRoute + '/' + id).then(function (response) {
                this.$destroy(true);
            }, function (response) {
                //error
            });
        },

        editCareTeamMember: function (id) {
            $("#editCareTeamModal-" + id).modal();
        },

        updateCareTeamMember: function (id) {
            this.$http.patch(this.updateRoute + '/' + id, {
                careTeamMember: this.care_person,
                patientId: this.patientId,
            }).then(function (response) {
                $("#editCareTeamModal-" + id).modal('hide');
            }, function (response) {
                //error
            });
        }
    }
});

module.exports = carePerson;