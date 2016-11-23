var Vue = require('vue');

Vue.use(require('vue-resource'));

// Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

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

    ready: function () {
        this.newLocations.push({});
    },

    methods: {
        addLocation: function () {
            if (this.sameEHRLogin) {
                this.newLocations.push({
                    ehrLogin: this.newLocations[0].ehrLogin
                });
            } else {
                this.newLocations.push({});
            }

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




