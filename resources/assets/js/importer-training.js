var Vue = require('vue');

Vue.config.debug = true;

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

        ready: function () {
            this.$set('practices', cpm.practices);
            this.$set('practice', cpm.predictedPracticeId);
            this.$set('location', cpm.predictedLocationId);
            this.$set('billingProvider', cpm.predictedBillingProviderId);
        },

        computed: {
            locations: function () {
                this.$set('locationsCollection', this.practices[this.practice].locations);

                return this.locationsCollection;
            },

            providers: function () {
                this.$set('providersCollection', this.locationsCollection[this.location].providersCollection[this.provider]);

                return this.providersCollection;
            }
        },

        methods: {}
    })
    ;




