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
            locations: [],
            providers: [],
            practice: '',
            location: '',
            billingProvider: '',
        },

        ready: function () {
            this.$set('practices', cpm.practices);
            this.$set('practice', cpm.predictedPracticeId);
            this.$set('location', cpm.predictedLocationId);
            this.$set('billingProvider', cpm.predictedBillingProviderId);
            this.$set('locations', this.practices[this.practice].locations);
            this.$set('providers', this.locations[this.location].providers[this.provider]);
        },

        methods: {}
    })
    ;




