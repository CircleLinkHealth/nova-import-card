var Vue = require('vue');
Vue.use(require('vue-resource'));
Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

require('./care-person.js');

var careTeam = Vue.component('careTeam', {

    events: {
        'billing-provider-changed': function (data) {
            let bp = data.oldBillingProvider;

            this.careTeamCollection = this.careTeamCollection.map(function (carePerson) {
                if (carePerson.id == bp.id) {
                    carePerson.formatted_type = 'External';
                }

                return carePerson;
            })
        }
    },

    template: '#care-team-template',

    props: [
        'careTeamCollection',
    ],

    data: function () {
        return {
            destroyRoute: '',
        }
    },

    mounted: function () {
        this.$set('destroyRoute', $('meta[name="provider-destroy-route"]').attr('content'));

        Vue.nextTick(function () {
            // DOM updated
        });
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
