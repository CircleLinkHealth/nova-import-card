var Vue = require('vue');

Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

/**
 *
 * VUE INSTANCE
 *
 */
var vm = new Vue({
        el: 'body',

        data: {
            practices: [],
            locationsCollection: [],
            providersCollection: [],
            practice: '',
            location: '',
            billingProvider: '',
        },

        mounted: function () {
            this.$set('practices', cpm.practices);
            this.$set('practice', cpm.predictedPracticeId);
            this.$set('location', cpm.predictedLocationId);
            this.$set('billingProvider', cpm.predictedBillingProviderId);

            Vue.nextTick(function () {
                // DOM updated
            });
        },

        computed: {
            locations: function () {
                this.$set('locationsCollection', this.practices[this.practice].locations);

                return this.locationsCollection;
            },

            providers: function () {
                this.$set('providersCollection', this.locations[this.location].providers);

                return this.providersCollection;
            }
        }
    })
;




