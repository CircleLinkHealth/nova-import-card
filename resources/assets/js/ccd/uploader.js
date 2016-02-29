var Vue = require('vue');
var Vmdl = require('vue-mdl');
var MDL = require('material-design-lite');

Vmdl.registerAll(Vue);

Vue.use(require('vue-resource'));

var uploader = new Vue({
    el: '#ccd-uploader',
    data: {
        ccdRecords: new FormData,
        ccdVendor: null,
        progress: 0,
        buffer: 100,
        message: 'Drop CCD Records in the box below, or click on it to browse your computer for CCDs. It is recommended that you import up to 5 CCDs in one go.',
        enabled: false, // submit button enabled
        formCssHide: true,
        groupHide: false,
        tableHide: true,
        qaSummaries: [],
        okToImport: new Array
    },
    ready: function () {
        this.watchForFileInput();
    },
    methods: {
        watchForFileInput: function () {
            $('input[type="file"]').change(this.notifyFileInput.bind(this));
            $('input[type="radio"]').change(this.notifyFileInput.bind(this));
        },
        notifyFileInput: function (e) {
            this.formCssHide = false;
            this.message = 'Preparing CCDs for upload';
            this.progress = 0;
            var files = e.target.files;

            for (var i = 0; i < files.length; i++) {
                this.ccdRecords.append('file[]', files[i]);
            }

            this.progress += 10;
            this.message = 'CCDs are ready for upload. Please click Upload CCD Records';
            this.enabled = true;
        },
        onSubmitForm: function (e) {
            e.preventDefault();
            this.enabled = false;
            this.message = 'Uploading CCD records.';
            this.progress += 20;

            this.ccdRecords.append('vendor', this.ccdVendor);

            this.$http.post('/ccd-importer/qaimport', this.ccdRecords, function (data, status, request) {
                this.ccdRecords = new FormData;

                this.qaSummaries = data;

                this.groupHide = true;
                this.tableHide = false;

                setTimeout(function () {
                    componentHandler.upgradeDom('MaterialCheckbox');
                }, 0);

                this.message = 'All operations completed successfully.';
                this.progress = 100;

            }).error(function (data, status, request) {
                this.message = 'ERROR! Uploading raw XML CCDs has failed. Try refreshing your browser.';
                console.log('Data: \n' + data);
                console.log('Status: \n' + status);
                console.log('Request: \n' + request);
            });
        },
        importCcds: function () {
            $('#importCcdsBtn').attr('disabled', true);
            this.$http.post('/ccd-importer/import', {qaImportIds: this.okToImport}, function (data, status, request) {
                $('#importCcdsBtn').attr('disabled', false);

                for (var i = 0; i < data.imported.length; i++) {
                    $('#checkbox-' + data.imported[i].qaId).html(
                        '<a target="_blank" href="https://'
                        + document.referrer.match(/:\/\/(.[^/]+)/)[1]
                        + '/manage-patients/patient-summary/?user='
                        + data.imported[i].userId
                        + '"><b style="color: #06B106">Go to CarePlan</b></a>'
                    );
                }
            }).error(function (data, status, request) {
                console.log('Data: \n' + data);
                console.log('Status: \n' + status);
                console.log('Request: \n' + request);
            });
        }
    }
});