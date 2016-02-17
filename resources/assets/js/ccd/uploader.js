var Vue = require('vue');
var Vmdl = require('vue-mdl');
var MDL = require('material-design-lite');

Vmdl.registerAll(Vue);

Vue.use(require('vue-resource'));

var uploader = new Vue({
    el: '#ccd-uploader',
    data: {
        ccdRecords: new FormData,
        progress: 0,
        buffer: 100,
        message: 'Drop CCD Records in the box below, or click on it to browse your computer for CCDs. It is recommended that you import up to 5 CCDs in one go.',
        enabled: true
    },
    ready: function () {
        this.watchForFileInput();
    },
    methods: {
        watchForFileInput: function () {
            $('input[type="file"]').change(this.notifyFileInput.bind(this));
        },
        notifyFileInput: function (e) {
            this.message = 'Preparing CCDs for upload';
            this.progress = 0;
            var files = event.target.files;
            var formData =  this.ccdRecords;

            for (var i = 0; i < files.length; i++) {
                formData.append('file[]', files[i]);
            }
            this.progress += 10;
            this.message = 'CCDs are ready for upload. Please click Upload CCD Records';
        },
        parseCCDwithBB: function (data) {
            var bb = BlueButton(data);

            var ccd = {
                document : bb.data.document,
                demographics : bb.data.demographics,
                allergies : bb.data.allergies,
                carePlan : bb.data.care_plan,
                chiefComplaint : bb.data.chief_complaint,
                encouters : bb.data.encounters,
                functionalStatuses : bb.data.functional_statuses,
                immunizations : bb.data.immunizations,
                immunizationDeclines : bb.data.immunization_declines,
                instructions : bb.data.instructions,
                results : bb.data.results,
                medications : bb.data.medications,
                problems : bb.data.problems,
                procedures : bb.data.procedures,
                smokingStatus : bb.data.smoking_status,
                vitals : bb.data.vitals,
            };
            return ccd;
        },
        onSubmitForm: function (e) {
            e.preventDefault();
            this.enabled = false;
            this.message = 'Uploading CCD records and checking for duplicates.';
            this.progress += 20;

            this.$http.post('/upload-raw-ccds', this.ccdRecords, function (data, status, request) {
                this.ccdRecords = new FormData;

                if (data.uploaded.length > 0) {
                    uploader.parseAndUploadCCDs(data.uploaded);
                }

                if (data.duplicates.length > 0) {
                    uploader.parseAndUploadDuplicateCCDs(data.duplicates);
                }

            }).error(function (data, status, request) {
                this.message = 'ERROR! Uploading raw XML CCDs has failed. Try refreshing your browser.'
                    + '<br>Details: ' + data + status + request;
            });
        },
        parseAndUploadCCDs: function (uploadedCCDs) {
            this.message = 'Parsing CCDs and generating Care Plans.';
            this.progress += 20;
            var parsedJsonCCDs = [];

            uploadedCCDs.forEach(function(ccd) {
                var jsonCcd = uploader.parseCCDwithBB(ccd.xml);
                var parsedCCD = {
                    userId:ccd.userId,
                    ccd:jsonCcd
                };
                parsedJsonCCDs.push(parsedCCD);
            });

            var json = JSON.stringify(parsedJsonCCDs);

            this.$http.post('/upload-parsed-ccds', json, function (data, status, request) {
                if (data) {
                    this.message = 'Everything completed successfully.';
                    this.progress = 100;
                    this.enabled = true;

                }
            }).error(function (data, status, request) {
                this.message = 'ERROR! Uploading Parsed CCDs has failed. Try refreshing your browser.'
                    + '<br>Details: ' + data + status + request;
            });
        },
        parseAndUploadDuplicateCCDs: function (duplicates) {
            this.message = 'Parsing duplicate CCDs and generating Care Plans.';
            this.progress += 15;
            var importAgain = [];

            var numberOfDuplicates = duplicates.length;

            if (numberOfDuplicates > 0) {
                for (var i = 0; i < numberOfDuplicates; i++) {
                    var duplicate = duplicates[i];
                    if (confirm(duplicate.fileName + " has already been imported. Do you wish to re-import it?")) {
                        var uniqueSuffix = '_*duplicate*_' + Date.now();
                        importAgain.push({
                            'blogId': duplicate.blogId,
                            'xml': duplicate.ccd,
                            'fullName': duplicate.fullName + uniqueSuffix,
                            'dob': duplicate.dob + uniqueSuffix,
                            'fileName': duplicate.fileName
                        });
                    }
                }
            }

            if (importAgain.length == numberOfDuplicates) {
                this.message = 'Uploading duplicate CCDs.';
                this.progress += 10;

                var json = JSON.stringify(importAgain);
                this.$http.post('/upload-duplicate-raw-ccds', json, function (data, status, request) {
                    uploader.parseAndUploadCCDs(data.uploaded);
                }).error(function (data, status, request) {
                    this.message = 'ERROR! Uploading duplicate raw XML CCDs has failed. Try refreshing your browser.'
                        + '\nData: ' + data + status + request;
                });
            }
        }
    }
});

function notification(text, type, element) {
    var notificationType = (type == 'success') ? 'alert-success' : 'alert-info';

    $(element).html(text)
        .toggleClass('hide')
        .toggleClass(notificationType)
        .toggleClass('fadeInRightBig')
        .delay(10000)
        .fadeOut(1000);
}