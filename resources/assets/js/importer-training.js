let Vue = require('vue');

Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

/**
 *
 * VUE INSTANCE
 *
 */
let vm = new Vue({
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
        Vue.nextTick(function () {
            vm.practices = cpm.practices;
            vm.practice = cpm.predictedPracticeId;
            vm.location = cpm.predictedLocationId;
            vm.billingProvider = cpm.predictedBillingProviderId;
        });
    },

    computed: {
        locations: function () {
            if (vm.practice === null) {
                Vue.nextTick(function () {
                    vm.location = null;
                    vm.billingProvider = null;
                    vm.providersCollection = [];
                });

                return [];
            }

            Vue.nextTick(function () {
                vm.locationsCollection = vm.practices[vm.practice].locations;
            });

            return vm.locationsCollection;
        },

        providers: function () {
            if (vm.location === null || !vm.practices[vm.practice].locations[vm.location]) {
                Vue.nextTick(function () {
                    vm.billingProvider = null;
                    vm.providersCollection = [];
                });

                return [];
            }

            Vue.nextTick(function () {
                vm.providersCollection = vm.locations[vm.location].providers;
            });

            return vm.providersCollection;
        }
    }
});




