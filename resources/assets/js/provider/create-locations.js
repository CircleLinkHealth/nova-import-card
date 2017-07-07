var Vue = require('vue');

Vue.use(require('vue-resource'));

//Load components
require('../components/CareTeam/search-providers.js');
require('../components/src/select.js');
require('../components/src/material-select.js');

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');


/**
 *
 * VUE INSTANCE
 *
 */
let locationsVM = new Vue({
    el: '#create-locations-component',

    data: function () {
        return {
            deleteTheseLocations: [],
            newLocations: [],

            sameClinicalIssuesContact: false,
            sameEHRLogin: false,

            patientClinicalIssuesContact: false,
            invalidCount: 0,

            timezoneOptions: [{
                name: 'Eastern Time',
                value: 'America/New_York'
            }, {
                name: 'Central Time',
                value: 'America/Chicago'
            }, {
                name: 'Mountain Time',
                value: 'America/Denver'
            }, {
                name: 'Mountain Time (no DST)',
                value: 'America/Phoenix'
            }, {
                name: 'Pacific Time',
                value: 'America/Los_Angeles'
            }, {
                name: 'Alaska Time',
                value: 'America/Anchorage'
            }, {
                name: 'Hawaii-Aleutian',
                value: 'America/Adak'
            }, {
                name: 'Hawaii-Aleutian Time (no DST)',
                value: 'Pacific/Honolulu'
            }]
        }
    },

    computed: {
        //Is the form fully filled out?
        formCompleted: function () {
            for (var index = 0; index < this.newLocations.length; index++) {

                this.isValidated(index);

                if (!this.newLocations[index].isComplete || this.newLocations[index].errorCount > 0) {
                    return false;
                }
            }

            return true;
        },

        showErrorBanner: function () {
            return this.invalidCount > 0;
        }
    },

    mounted: function () {
        Vue.nextTick(function () {
            let len = cpm.existingLocations.length;

            for (let i = 0; i < len; i++) {
                Vue.set(locationsVM.newLocations, i, cpm.existingLocations[i]);

                if (i === 0) {
                    locationsVM.sameClinicalIssuesContact = cpm.existingLocations[i].sameClinicalIssuesContact;
                    locationsVM.sameEHRLogin = cpm.existingLocations[i].sameEHRLogin;
                }
            }

            if (len < 1) {
                locationsVM.create();
            }

            $('select').material_select();
            $('.collapsible').collapsible();
        });
    },

    methods: {
        create: function () {
            this.newLocations.push({
                clinical_contact: {
                    email: '',
                    firstName: '',
                    lastName: '',
                    type: 'billing_provider'
                },
                timezone: 'America/New_York',
                ehr_password: '',
                city: '',
                address_line_1: '',
                address_line_2: '',
                ehr_login: '',
                errorCount: 0,
                isComplete: false,
                name: '',
                phone: '',
                fax: '',
                emr_direct_address: '',
                postal_code: '',
                state: '',
                validated: false
            });
        },

        //Is the form for the given user filled out?
        isValidated: function (index) {
            Vue.nextTick(function () {
                locationsVM.invalidCount = $('.invalid').length;

                locationsVM.newLocations[index].isComplete = locationsVM.newLocations[index].name
                    && locationsVM.newLocations[index].address_line_1
                    && locationsVM.newLocations[index].city
                    && locationsVM.newLocations[index].state
                    && locationsVM.newLocations[index].postal_code;

                locationsVM.newLocations[index].errorCount = $('#location-' + index).find('.invalid').length;
                locationsVM.newLocations[index].validated = locationsVM.newLocations[index].isComplete && locationsVM.newLocations[index].errorCount === 0;
            });
            
            return this.newLocations[index].validated;
        },

        addLocation: function () {
            this.submitForm($('meta[name="submit-url"]').attr('content'));

            this.create();

            Vue.nextTick(function () {
                $('select').material_select();
                $('.collapsible').collapsible();
            });
        },

        deleteLocation: function (index) {
            if (this.newLocations[index].id) {
                this.deleteTheseLocations.push(this.newLocations[index].id);
            }

            this.newLocations.splice(index, 1);
        },

        submitForm: function (url) {
            this.$http.post(url, {
                deleteTheseLocations: this.deleteTheseLocations,
                locations: this.newLocations,
                sameClinicalIssuesContact: this.sameClinicalIssuesContact,
                sameEHRLogin: this.sameEHRLogin,

            }).then(function (response) {
                // success
                if (response.data.redirect_to) {
                    window.location.href = response.data.redirect_to;
                }

                if (response.data.message) {
                    Materialize.toast(response.data.message, 4000);
                }
            }, function (response) {
                //fail

                let created = response.data.created.map(function (index) {
                    locationsVM.newLocations.splice(index, 1);
                });

                let errors = response.data.errors;

                locationsVM.$set('invalidCount', errors.length);

                for (let i = 0; i < errors.length; i++) {
                    $('input[name="locations[' + i + '][' + Object.keys(errors[i].messages)[0] + ']"]')
                        .addClass('invalid');

                    $('label[for="locations[' + i + '][' + Object.keys(errors[i].messages)[0] + ']"]')
                        .attr('data-error', errors[i].messages[Object.keys(errors[i].messages)[0]][0]);

                    locationsVM.$set('newLocations[' + i + '].errorCount', errors.length);
                }

                $("html, body").animate({scrollTop: 0}, {duration: 300, queue: false});
            });
        }
    }
});




