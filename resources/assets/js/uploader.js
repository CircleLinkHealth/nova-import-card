var Vue = require('vue');
var Vmdl = require('vue-mdl');
// var MDL = require('material-design-lite');

Vue.config.debug = true;

Vmdl.registerAll(Vue);

Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

/**
 *
 * CCD UPLOADER COMPONENT
 *
 */
var CcdUploader = Vue.extend({
    template: require('./components/ccd-uploader.template.html'),

    data: function () {
        return {
            blogs: [],
            ccdVendors: [],
            selectedVendor: null,
            selectedProgram: null,
            progress: 0,
            buffer: 100,
            message: 'Drop CCD Records in the box below, or click on it to browse your computer for CCDs. It is recommended that you import up to 5 CCDs in one go.',
            enabled: false, // submit button enabled
            csrfToken: null
        }
    },

    ready: function () {
        this.watchForFileInput();
        this.blogs = window.cpm.userBlogs;
        this.ccdVendors = window.cpm.ccdVendors;
        this.csrfToken = $('meta[name="csrf-token"]').attr('content');
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
            okToImport: [],
            okToDelete: []
        }
    },

    ready: function () {
        this.qaSummaries = window.cpm.qaSummaries;
    },

    methods: {
        syncCcds: function () {
            $('#syncCcdsBtn').attr('disabled', true);

            var payload = {
                ccdsToImport: this.okToImport,
                ccdsToDelete: this.okToDelete
            };

            this.$http.post('/ccd-importer/import', payload, function (data, status, request) {
                if (data.imported) {
                    for (var i = 0; i < data.imported.length; i++) {
                        $('#import-row-' + data.imported[i].ccdaId).html(
                            '<a target="_blank" href="https://'
                            + window.location.href.match(/:\/\/(.[^/]+)/)[1]
                            + '/manage-patients/'
                            + data.imported[i].userId
                            + '/summary'
                            + '"><b style="color: #06B106">Go to CarePlan</b></a>'
                        );
                        $('#delete-row-' + data.imported[i].ccdaId).html('N/A');
                        $('#edit-row-' + data.imported[i].ccdaId).html('N/A');
                    }
                    this.okToImport = [];
                }

                if (data.deleted) {
                    for (var i = 0; i < data.deleted.length; i++) {
                        var target = $('#row-' + data.deleted[i]);
                        target.hide('slow', function () {
                            target.remove();
                        });
                        this.okToDelete = [];
                    }
                }

            }).error(function (data, status, request) {
                console.log('Data: \n' + data);
                console.log('Status: \n' + status);
                console.log('Request: \n' + request);
            });
        },
        toggleCheckboxes: function (event) {
            //get id of clicked element
            var ccdaId = event.target.id.split('-')[1];
            var itemClicked = event.target.id.split('-')[0];

            var importLabel = $('#import-label-' + ccdaId);
            var deleteLabel = $('#delete-label-' + ccdaId);

            if (itemClicked == 'delete' && this.okToDelete.indexOf(ccdaId) == -1 && importLabel.hasClass('is-checked')) {
                if (this.okToImport.indexOf(ccdaId) !== -1) {
                    this.okToImport.$remove(ccdaId);
                    importLabel.toggleClass('is-checked');
                }
            }

            if (itemClicked == 'import' && this.okToImport.indexOf(ccdaId) == -1 && deleteLabel.hasClass('is-checked')) {
                if (this.okToDelete.indexOf(ccdaId) !== -1) {
                    this.okToDelete.$remove(ccdaId);
                    deleteLabel.toggleClass('is-checked');
                }
            }
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
            program: '',
            providers: '',
            vendor: '',

            enableButton: true
        }
    },

    ready: function () {
        this.demographics = window.cpm.demographics;
        //this.allergies = window.cpm.allergies;
        //this.medications = window.cpm.medications;
        //this.problems = window.cpm.problems;
        this.locations = window.cpm.locations;
        this.program = window.cpm.program;
        this.providers = window.cpm.providers;
        this.vendor = window.cpm.vendor;
    },

    methods: {
        submitForm: function () {
            this.enableButton = false;

            var payload = {
                demographics: this.demographics
            };

            this.$http.post('/ccd-importer/demographics', payload).then(function (response) {
                alert('It seems like it went well. Click Back to Summary Page (top right of this window), ' +
                    'to return to the summary page and import the CCD.');

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




