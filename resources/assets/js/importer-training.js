require('./bootstrap');

window.Vue = require('vue');

window.axios.defaults.baseURL = $('meta[name="base-url"]').attr('content');

window.App = new Vue({
    el: '#trainer-results',

    data: {
        practices: cpm.practices,
        locationsCollection: [],
        providersCollection: [],
        practice: cpm.predictedPracticeId,
        location: cpm.predictedLocationId,
        billingProvider: cpm.predictedBillingProviderId,
    },

    computed: {
        locations: function () {
            if (_.isNull(this.practice)) {
                this.location = null;
                this.billingProvider = null;
                this.providersCollection = [];

                return [];
            }

            this.locationsCollection = this.practices[this.practice].locations;

            return this.locationsCollection;
        },

        providers: function () {
            if (this.location === null || !this.practices[this.practice].locations[this.location]) {
                this.billingProvider = null;
                this.providersCollection = [];

                return [];
            }

            this.providersCollection = this.locations[this.location].providers;

            return this.providersCollection;
        }
    }
});




