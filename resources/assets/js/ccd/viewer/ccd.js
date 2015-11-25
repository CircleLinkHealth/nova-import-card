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

Vue.filter('full_name', require('./filters/full_name.js'));
Vue.filter('isolanguage', require('./filters/isolanguage.js'));


