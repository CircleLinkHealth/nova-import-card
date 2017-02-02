var Vue = require('vue');
Vue.use(require('vue-resource'));
require('./components/CareTeam/care-team.js');

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');


Vue.directive("select2", {
    "twoWay": true,

    "bind": function () {
        $(this.el).select2();

        var self = this;

        $(this.el).on('change', function () {
            self.set($(self.el).val());
        });
    },

    update: function (newValue, oldValue) {
        $(this.el).val(newValue);
    },

    "unbind": function () {
        $(this.el).select2('destroy');
    }
});


/**
 *
 * VUE INSTANCE
 *
 */
const vm = new Vue({
    el: 'body',

    data: {
        careTeamCollection: [],
    },

    events: {
        'care-person-created': function (data) {
            this.careTeamCollection.push(data.newCarePerson);
        }
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

            this.$dispatch('care-person-created', {
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

            this.$nextTick(function () {
                $("#editCareTeamModal-" + id).modal();
            });
        },
    }
});



