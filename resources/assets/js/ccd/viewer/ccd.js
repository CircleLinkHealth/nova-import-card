var Vue = require('vue');
Vue.use(require('vue-resource'));

new Vue({
    el: 'body',
    data: {
        bb: '',
        document: '',
        allergies: '',
        careplan: '',
        chiefComplaint: '',
        demographics: '',
        encounters: '',
        functionalStatuses: '',
        immunizations: '',
        instructions: '',
        labResults: '',
        medications: '',
        problems: '',
        procedures: '',
        smokingStatus: '',
        vitals: ''
    },
    mounted: function () {
        this.loadCCD();

        Vue.nextTick(function () {
            // DOM updated
        });
    },
    methods: {
        loadCCD: function () {
            var resource = this.$resource('getVueVar/:id');

            resource.get({id: 430}, function(ccdRecord) {
                var bb = BlueButton(ccdRecord);
                this.$set('bb', bb.data);
                this.$set('document', bb.data.document);
                this.$set('allergies', bb.data.allergies);
                this.$set('careplan', bb.data.care_plan);
                this.$set('chiefComplaint', bb.data.chief_complaint);
                this.$set('demographics', bb.data.demographics);
                this.$set('encounters', bb.data.encounters);
                this.$set('functionalStatuses', bb.data.functional_statuses);
                this.$set('immunizations', bb.data.immunizations);
                this.$set('instructions', bb.data.instructions);
                this.$set('labResults', bb.data.results);
                this.$set('medications', bb.data.medications);
                this.$set('problems', bb.data.problems);
                this.$set('procedures', bb.data.procedures);
                this.$set('smokingStatus', bb.data.smoking_status);
                this.$set('vitals', bb.data.vitals);
            }).error(function (data, status) {
                console.log(status + ' error: ' + data);
            });
        }
    }
});


Vue.filter('age', require('./filters/age.js'));
Vue.filter('display_name', require('./filters/display_name.js'));
Vue.filter('fallback', require('./filters/fallback.js'));
Vue.filter('full_name', require('./filters/full_name.js'));
Vue.filter('gender_pronoun', require('./filters/gender_pronoun.js'));
Vue.filter('iso_language', require('./filters/iso_language.js'));
Vue.filter('max_severity', require('./filters/max_severity.js'));
Vue.filter('related_by_date', require('./filters/related_by_date.js'));
Vue.filter('since_days', require('./filters/since_days.js'));
Vue.filter('strict_length', require('./filters/strict_length.js'));


