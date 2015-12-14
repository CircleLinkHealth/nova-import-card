var Vue = require('vue');
Vue.use(require('vue-resource'));

var uploader = new Vue({
    el: '#ccd-uploader',
    data: {
        ccdRecords: new FormData,
        parsedJsonCCDs: []
    },
    ready: function () {
        this.watchForFileInput();
    },
    methods: {
        watchForFileInput: function () {
            $('input[type="file"]').change(this.notifyFileInput.bind(this));
        },
        notifyFileInput: function (e) {
            var files = event.target.files;
            var formData =  this.ccdRecords;

            for (var i = 0; i < files.length; i++) {
                formData.append('file[]', files[i]);
            }
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

            this.$http.post('/upload-raw-ccds', this.ccdRecords, function (data, status, request) {
                data.forEach(function(ccd) {
                    var jsonCcd = uploader.parseCCDwithBB(ccd.xml);

                    var parsedCCD = {
                        userId:ccd.userId,
                        ccd:jsonCcd
                    };
                    uploader.parsedJsonCCDs.push(parsedCCD);
                });

                var json = JSON.stringify(uploader.parsedJsonCCDs);

                this.$http.post('/upload-parsed-ccds', json, function (data, status, request) {
                    console.log('Success: ' + data + status + request);
                    alert('Success: ' + data);
                }).error(function (data, status, request) {
                    console.log('Error: ' + data + status + request);
                    alert('Error: ' + data);
                });

            }).error(function (data, status, request) {
                console.log('Error: ' + data + status + request);
                alert('Error: ' + data);
            });
        }
    },
});