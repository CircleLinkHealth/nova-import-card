require('../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/bootstrap');
require('../../../../public/js/materialize.min');

window.Vue = require('vue');

Vue.component('material-select', require('../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/src/material-select.vue'));

const locationsVM = new Vue({
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
            for (let index = 0; index < this.newLocations.length; index++) {

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
        });
    },

    methods: {
        create: function () {
            this.newLocations.push({
                clinical_contact: {
                    email: '',
                    first_name: '',
                    last_name: '',
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
        },

        deleteLocation: function (index) {
            if (this.newLocations[index].id) {
                this.deleteTheseLocations.push(this.newLocations[index].id);
            }

            this.newLocations.splice(index, 1);
        },

        submitForm: function (url) {

            let self = this;

            window.axios.post(url, {
                deleteTheseLocations: this.deleteTheseLocations,
                locations: this.newLocations,
                sameClinicalIssuesContact: this.sameClinicalIssuesContact,
                sameEHRLogin: this.sameEHRLogin,

            }).then((response) => {
                // success
                if (response.data.redirect_to) {
                    window.location.href = response.data.redirect_to;
                }

                if (response.data.message) {
                    Materialize.toast(response.data.message, 4000);
                }
            })
                .catch((error) => {
                        if (error.response) {
                            console.log(error.response);
                        } else {
                            console.log('Error', error.message);
                        }


                        let response = error.response;

                        let created = response.data.created.map(function (index) {
                            locationsVM.newLocations.splice(index, 1);
                        });

                        let errors = response.data.errors;

                        self.invalidCount = errors.length;

                        for (let i = 0; i < errors.length; i++) {
                            $('input[name="locations[' + errors[i].index + '][' + Object.keys(errors[i].messages)[0] + ']"]')
                                .addClass('invalid');

                            $('label[for="locations[' + errors[i].index + '][' + Object.keys(errors[i].messages)[0] + ']"]')
                                .attr('data-error', errors[i].messages[Object.keys(errors[i].messages)[0]][0]);

                            self.newLocations[errors[i].index].errorCount = errors.length;
                        }

                        $("html, body").animate({scrollTop: 0}, {duration: 300, queue: false});
                    }
                );

        }
    }
});




