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
        this.newLocations.push({
            clinical_contact: {
                firstName: '',
                lastName: '',
                email: '',
            }
        });
    },

    methods: {
        //Is the form for the given user filled out?
        isValidated: function (index) {
            this.$set('invalidCount', $('.invalid').length);

            this.$set('newLocations[' + index + '].isComplete', this.newLocations[index].name
                && this.newLocations[index].address_line_1
                && this.newLocations[index].city
                && this.newLocations[index].state
                && this.newLocations[index].postal_code
                && (this.newLocations[index].ehr_login || this.sameEHRLogin)
                && (this.newLocations[index].ehr_password || this.sameEHRLogin)
            );

            this.$set('newLocations[' + index + '].errorCount', $('#location-' + index).find('.invalid').length);
            this.$set('newLocations[' + index + '].validated', this.newLocations[index].isComplete && this.newLocations[index].errorCount == 0);

            return this.newLocations[index].validated;
        },

        addLocation: function () {

            this.newLocations.push({});

            this.$nextTick(function () {
                $('select').material_select();
                $('.collapsible').collapsible();
            });
        },

        deleteLocation: function (index) {
            this.newLocations.splice(index, 1);
        }
    }
});




