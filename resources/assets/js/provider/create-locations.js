var Vue = require('vue');

Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

Vue.directive("select", {
    "twoWay": true,

    "bind": function () {
        $(this.el).material_select();

        var self = this;

        $(this.el).on('change', function () {
            self.set($(self.el).val());
        });
    },

    update: function (newValue, oldValue) {
        $(this.el).val(newValue);
    },

    "unbind": function () {
        $(this.el).material_select('destroy');
    }
});

/**
 *
 * VUE INSTANCE
 *
 */
var locationsVM = new Vue({
    el: '#create-locations-component',

    data: function () {
        return {
            deleteTheseLocations: [],
            newLocations: [],

            sameEHRLogin: false,
            sameClinicalIssuesContact: false,

            patientClinicalIssuesContact: false
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
            if (this.invalidCount > 0) {
                return true;
            }
        }
    },

    ready: function () {
        this.create();
    },

    methods: {
        create: function () {
            this.newLocations.push({
                clinical_contact: {
                    firstName: '',
                    lastName: '',
                    email: '',
                    type: 'billing_provider'
                },
                timezone: 'America/New_York'
            });
        },

        //Is the form for the given user filled out?
        isValidated: function (index) {
            this.$set('invalidCount', $('.invalid').length);

            this.$set('newLocations[' + index + '].isComplete', this.newLocations[index].name
                && this.newLocations[index].address_line_1
                && this.newLocations[index].city
                && this.newLocations[index].state
                && this.newLocations[index].postal_code
            );

            this.$set('newLocations[' + index + '].errorCount', $('#location-' + index).find('.invalid').length);
            this.$set('newLocations[' + index + '].validated', this.newLocations[index].isComplete && this.newLocations[index].errorCount == 0);

            return this.newLocations[index].validated;
        },

        addLocation: function () {
            this.create();

            this.$nextTick(function () {
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
                locations: this.newLocations
            }).then(function (response) {
                // success
                window.location.href = response.data.redirect_to;
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




