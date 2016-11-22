var Vue = require('vue');

Vue.use(require('vue-resource'));

/**
 *
 * CREATE PRACTICE VUE INSTANCE
 *
 */
var createPractice = new Vue({
    el: '#create-practice-component',

    data: function () {
        return {
            //For variables prefixed with many:
            //  true => different value for each location
            //  false => same value for all locations
            manyEHRLogins: false,
            manyClinicalIssuesContacts: false,

            patientClinicalIssuesContact: false
        }
    },

    ready: function () {

    },

    methods: {}
});




