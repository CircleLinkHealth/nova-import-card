var Vue = require('vue');
Vue.use(require('vue-resource'));

new Vue({
    el: 'body',
    data: {
        document: '',
        demographics: ''
    },
    ready: function () {
        this.loadCCD();
    },
    methods: {
        loadCCD: function () {
            this.$http.get('getVueVar', function(ccdRecord) {
                var bb = BlueButton(ccdRecord);
                this.$set('document', bb.data.document);
                this.$set('demographics', bb.data.demographics);
            }).error(function (data, status, request) {
                console.log('error');
            });
        }
    }
});

Vue.filter('age', require('./filters/age.js'));
Vue.filter('display_name', require('./filters/display_name.js'));
Vue.filter('full_name', require('./filters/full_name.js'));
Vue.filter('gender_pronoun', require('./filters/gender_pronoun.js'));
Vue.filter('iso_language', require('./filters/iso_language.js'));
Vue.filter('max_severity', require('./filters/max_severity.js'));
Vue.filter('since_days', require('./filters/since_days.js'));
Vue.filter('strict_length', require('./filters/strict_length.js'));


