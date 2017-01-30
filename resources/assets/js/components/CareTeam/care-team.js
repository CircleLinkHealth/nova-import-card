var Vue = require('vue');
Vue.use(require('vue-resource'));
Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

require('./care-person.js');

var careTeam = Vue.component('careTeam', {
    template: '#care-team-template',

    props: [
        'careTeamCollection',
    ],

    data: function () {
        return {
            destroyRoute: '',
        }
    },

    ready: function () {
        this.$set('destroyRoute', $('meta[name="provider-destroy-route"]').attr('content'));
    },

    methods: {
        deleteCarePerson: function (carePerson, index) {
            let disassociate = confirm('Are you sure you want to remove ' + carePerson.user.first_name
                + ' '
                + carePerson.user.last_name + ' from the CareTeam?');

            if (!disassociate) {
                return true;
            }

            this.$http.delete(this.destroyRoute + '/' + carePerson.id).then(function (response) {
                this.careTeamCollection.splice(index, 1);
            }, function (response) {
                //error
            });
        },

        editCarePerson: function (carePerson) {
            $("#editCareTeamModal-" + carePerson.id).modal();
        },
    }
});

module.exports = careTeam;
