/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
window.Vue.use(require('vue-resource'));

/**
 *
 * VUE INSTANCE
 *
 */
const vm = new Vue({
    el: '#trainer-results',

    data: {
        practices: [],
        locationsCollection: [],
        providersCollection: [],
        practice: null,
        location: null,
        billingProvider: null,
    },

    mounted: function () {
        this.practices = cpm.practices;
        this.practice = cpm.predictedPracticeId;
        this.location = cpm.predictedLocationId;
        this.billingProvider = cpm.predictedBillingProviderId;
    },

    computed: {
        locations: function () {
            let self = this;
            
            if (self.practice === null) {
                Vue.nextTick(function () {
                    self.location = null;
                    self.billingProvider = null;
                    self.providersCollection = [];
                });

                return [];
            }

            Vue.nextTick(function () {
                self.locationsCollection = self.practices[self.practice].locations;
            });

            return self.locationsCollection;
        },

        providers: function () {
            let self = this;
            
            if (self.location === null || !self.practices[self.practice].locations[self.location]) {
                Vue.nextTick(function () {
                    self.billingProvider = null;
                    self.providersCollection = [];
                });

                return [];
            }

            Vue.nextTick(function () {
                self.providersCollection = self.locations[self.location].providers;
            });

            return self.providersCollection;
        }
    }
});




