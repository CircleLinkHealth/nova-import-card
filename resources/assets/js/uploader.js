var Vue = require('vue');
var Vmdl = require('vue-mdl');
var MDL = require('material-design-lite');

Vue.config.debug = true;

Vmdl.registerAll(Vue);

Vue.use(require('vue-resource'));

/**
 *
 * CCD UPLOADER COMPONENT
 *
 */
var CcdUploader = Vue.extend({
    template: require('./components/ccd-uploader.template.html'),

    data: function () {
        return {
            blogs: new Array,
            ccdVendors: new Array,
            selectedVendor: null,
            progress: 0,
            buffer: 100,
            message: 'Drop CCD Records in the box below, or click on it to browse your computer for CCDs. It is recommended that you import up to 5 CCDs in one go.',
            enabled: false, // submit button enabled
        }
    },

    ready: function () {
        this.watchForFileInput();
        this.blogs = window.cpm.userBlogs;
        this.ccdVendors = window.cpm.ccdVendors;
    },

    methods: {
        watchForFileInput: function () {
            $('input[type="file"]').change(this.notifyFileInput.bind(this));
            $('input[type="radio"]').change(this.notifyFileInput.bind(this));
        },

        notifyFileInput: function (e) {
            this.message = 'Preparing CCDs for upload';
            this.progress = 0;
            this.progress += 10;
            this.message = 'CCDs are ready for upload. Please click Upload CCD Records';
            this.enabled = true;
        },

        onSubmitForm: function (e) {
            jQuery("body").html("<img style=\"text-align: center;\" src=\"https://media.giphy.com/media/hA9LnXzyZTxmg/giphy.gif\"/>");
        }
    }
});

/**
 *
 * UPLOADED CCD PANEL COMPONENT
 *
 */
var UploadedCcdsPanel = Vue.extend({
    template: require('./components/ccd-uploaded-summary.template.html'),

    data: function () {
        return {
            qaSummaries: [],
            okToImport: new Array
        }
    },

    ready: function () {
        this.qaSummaries = window.cpm.qaSummaries;
    },

    methods: {
        importCcds: function () {
            $('#importCcdsBtn').attr('disabled', true);

            this.$http.post('/ccd-importer/import', {ccdaIds: this.okToImport}, function (data, status, request) {
                $('#importCcdsBtn').attr('disabled', false);

                for (var i = 0; i < data.imported.length; i++) {
                    $('#checkbox-' + data.imported[i].qaId).html(
                        '<a target="_blank" href="https://'
                        + window.location.href.match(/:\/\/(.[^/]+)/)[1]
                        + '/manage-patients/'
                        + data.imported[i].userId
                        + '/summary'
                        + '"><b style="color: #06B106">Go to CarePlan</b></a>'
                    );
                }
            }).error(function (data, status, request) {
                console.log('Data: \n' + data);
                console.log('Status: \n' + status);
                console.log('Request: \n' + request);
            });
        },
        fetchImportedInfo: function (event) {
            var ccdaId = event.target.id.split('-')[1];

            this.$http.post('/ccd-importer/qa-imported', {ccdaId: ccdaId}, function (data, status, request) {
                console.log(data);
            }).error(function (data, status, request) {
                console.log('Data: \n' + data);
                console.log('Status: \n' + status);
                console.log('Request: \n' + request);
            });
        }
    }
});


/**
 *
 * EDIT UPLOADED ITEMS COMPONENT
 *
 */
var EditUploadedItems = Vue.extend({
    template: require('./components/edit-uploaded-ccd-items.template.html')
});


/**
 *
 * DEMOGRAPHICS COMPONENT
 *
 */
var Demographics = Vue.extend({
    template: require('./components/edit-demographics.template.html'),

    data: function () {
        return {
            demographics: '',
            //allergies: null,
            //medications: null,
            locations: '',
            providers: '',

            enableButton: true
        }
    },

    ready: function () {
        this.demographics = window.cpm.demographics;
        //this.allergies = window.cpm.allergies;
        //this.medications = window.cpm.medications;
        //this.problems = window.cpm.problems;
        this.locations = window.cpm.locations;
        this.providers = window.cpm.providers;
    },

    methods: {
        submitForm: function () {
            this.enableButton = false;

            var substitutedId = this.demographics.id;

            delete this.demographics.id;

            var payload = {
                substitutedId: substitutedId,
                demographics: this.demographics
            };

            this.$http.post('/ccd-importer/demographics', payload).then(function (response) {
                alert('It seems like it went well. Click back and import this CCD. You will not see the changes you made ' +
                    'reflected on the summary screen yet (because Apri..*cough**cough*). Your changes will be seen in the careplan.' +
                    'Expect more progress soon.');

            }, function (response) {
                console.log(response);
            });

            this.enableButton = true;
        }
    }
});

/**
 *
 * TEXT WITH FLOATING LABEL COMPONENT
 *
 */
var TextWithFloatingLabel = Vue.extend({
    props: {
        label: {},
        model: {}
    },

    template: require('./components/UI/text-with-floating-label.template.html')
});

/**
 *
 * FILTERS
 *
 */
Vue.filter('snakeToNormal', function (value) {
    return value ? value.split('_').join(' ') : value;
});

/**
 *
 * REGISTER GLOBAL COMPONENTS
 *
 */
Vue.component('ccd-uploader', CcdUploader);
Vue.component('edit-demographics', Demographics);
Vue.component('edit-uploaded-ccd-items', EditUploadedItems);
Vue.component('text-with-floating-label', TextWithFloatingLabel);
Vue.component('uploaded-ccd-panel', UploadedCcdsPanel);


/**
 *
 * VUE INSTANCE
 *
 */
var vm = new Vue({
    el: 'body'
});




