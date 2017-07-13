var Vue = require('vue');
Vue.use(require('vue-resource'));

import VueForm from 'vue-form';

Vue.use(VueForm, {
    inputClasses: {
        valid: 'form-control-success',
        invalid: 'form-control-danger'
    }
});

require('./components/CareTeam/care-team.vue');
require('./components/src/select2.vue');

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

// This is the event hub we'll use in every
// component to communicate between them.
let eventHub = new Vue();

/**
 *
 * VUE INSTANCE
 *
 */
const vm = new Vue({
    el: '#app',

    data: {
        careTeamCollection: [],
    },


    created: function () {
        eventHub.$on('care-person-created', this.addCarePerson);
    },

    beforeDestroy: function () {
        eventHub.$off('care-person-created', this.addCarePerson);
    },

    mounted: function () {
        this.careTeamCollection = cpm.careTeam;
    },

    methods: {
        addCarePerson: function (data) {
            vm.careTeamCollection.push(data.newCarePerson);
        },

        createCarePerson: function () {
            let id = 'new' + parseInt((Math.random() * 100), 10);

            eventHub.$emit('care-person-created', {
                newCarePerson: {
                    id: id,
                    formatted_type: 'External',
                    alert: false,
                    is_billing_provider: false,
                    user: {
                        id: '',
                        email: '',
                        first_name: '',
                        last_name: '',
                        address: '',
                        address2: '',
                        city: '',
                        state: '',
                        zip: '',
                        phone_numbers: {
                            0: {
                                id: '',
                                number: '',
                            }
                        },
                        primary_practice: {
                            id: '',
                            display_name: ''
                        },
                        provider_info: {
                            id: '',
                            qualification: '',
                            specialty: '',
                        }
                    },
                },
            });

            Vue.nextTick(function () {
                $("#editCareTeamModal-" + id).modal();
            });
        },
    }
});



