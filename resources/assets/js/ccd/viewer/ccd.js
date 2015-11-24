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
    },
    filters: {
        full_name: function (input) {
            if (typeof input.given == 'undefined') {
                return "John Doe";
            }
            if (input.given === null) {
                if (input.family === null) {
                    return "Unknown";
                } else {
                    return input.family;
                }
            }
            var name, first_given, other_given, names = input.given.slice(0);
            var prefix = (input.prefix === null) ? '' : input.prefix;
            var suffix = (input.suffix === null) ? '' : input.suffix;
            if (names instanceof Array) {
                first_given = names.splice(0, 1);
                other_given = names.join(" ");
            } else {
                first_given = names;
            }
            name = first_given;
            name = input.call_me ? name + " \"" + input.call_me + "\"" : name;
            name = (other_given) ? name + " " + other_given : name;
            name = prefix + " " + name + " " + input.family + " " + suffix;
            return name;
        }
    }
});

