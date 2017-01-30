var Vue = require('vue');
Vue.use(require('vue-resource'));
require('./components/CareTeam/search-providers.js');
require('./components/CareTeam/care-person.js');

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

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
        if (typeof cpm !== 'undefined') {
            for (var i = 0, len = cpm.careTeam.length; i < len; i++) {
                this.careTeamCollection.$set(i, cpm.careTeam[i]);
            }
        }
    },

    methods: {
        createCarePerson: function () {
            let id = 'new' + parseInt((Math.random() * 100), 10);

            this.careTeamCollection.push({
                id: id,
                formatted_type: 'External',
                alert: '',
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




