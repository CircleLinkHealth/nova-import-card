var Vue = require('vue');

Vue.config.debug = true;

Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

var carePerson = Vue.component('carePerson', {
    template: '#care-person-template',

    props: [
        //This is an App\CarePerson Object with relationships User and ProviderInfo loaded.
        'care_person',
    ],

    data: function () {
        return {
            destroyRoute: '',
            updateRoute: '',
        }
    },

    ready: function () {
        this.$set('destroyRoute', $('meta[name="provider-destroy-route"]').attr('content'));
        this.$set('updateRoute', $('meta[name="provider-update-route"]').attr('content'));
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
            this.$http.patch(this.updateRoute + '/' + id, {careTeamMember: this.care_person}).then(function (response) {
                $("#editCareTeamModal-" + id).modal('hide');
            }, function (response) {
                //error
            });
        }
    }
});

var careTeamContainer = {};

/**
 *
 * VUE INSTANCE
 *
 */
var vm = new Vue({
    el: 'body',

    data: {
        careTeamCollection: [],
    },

    ready: function () {
        for (var i = 0, len = cpm.careTeam.length; i < len; i++) {
            this.careTeamCollection.$set(i, cpm.careTeam[i]);
        }
    },

    methods: {
        createCarePerson: function () {
            let id = 'new' + parseInt((Math.random() * 100), 10);

            this.careTeamCollection.push({
                id: id,
                formatted_type: 'External',
                user: {
                    first_name: '',
                    last_name: '',
                    phone_numbers: {
                        0: {
                            number: '',
                        }
                    },
                    primary_practice: {
                        display_name: ''
                    },
                    provider_info: {
                        specialty: '',
                    }
                },
            });

            this.$nextTick(function () {
                $("#editCareTeamModal-" + id).modal();
            });
        }
    }
});




