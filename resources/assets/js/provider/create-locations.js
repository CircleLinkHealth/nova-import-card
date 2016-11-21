var Vue = require('vue');

Vue.use(require('vue-resource'));

// Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

/**
 *
 * VUE INSTANCE
 *
 */
var vm = new Vue({
    el: '#create-locations-component',

    data: function () {
        return {}
    },

    ready: function () {
        console.log('yo');
    },

    methods: {}
});




