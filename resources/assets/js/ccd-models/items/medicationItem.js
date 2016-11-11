var Vue = require('vue');

Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

var patientId = $('#patient_id').val();

new Vue({
    el: '#medications',
    data: {
        medication: { id: '', patient_id: patientId, name: '', sig: '' }
    },

    ready: function() {
        this.loadMedications();
    },

    methods: {

        loadMedications: function() {
            var payload = {
                'patient_id': $('#patient_id').val()
            };

            this.$http.get('/CCDModels/Items/MedicationListItem', {params: payload}).then(function (response) {
                this.$set('problems', response.data);
            }, function (response) {
                console.log(response);
            });
        },

        addMedication: function() {
            if(this.medication.name) {
                var payload = {
                    'medication': this.medication
                };

                this.$http.post('/CCDModels/Items/MedicationListItem/store', payload).then(function (response) {
                    var id = response.data.id.id;
                    var patient_id = $('#patient_id').val();
                    this.medications.push({ id: id, patient_id: patient_id, name: response.data.id.name, sig: response.data.id.sig });
                    this.medication = { id: '', patient_id: patient_id, name: '', sig: '' };
                }, function (response) {
                    console.log(response);
                });
            }
        },

        editMedication: function(index) {
            // hide text
            $('#medication-name-' + index).toggle();
            $('#medication-sig-' + index).toggle();

            // show textarea for editing
            $('#medication-edit-' + index).toggle();
            $('#medication-edit-sig-' + index).toggle();

            // hide edit/delete buttons
            $('.medication-edit-btn').hide();
            $('.medication-delete-btn').hide();

            // show save button
            $('#medication-save-btn-' + index).toggle();
        },

        updateMedication: function(index) {
            var payload = {
                'medication': this.medications[index]
            };

            this.$http.post('/CCDModels/Items/MedicationListItem/update', payload).then(function (response) {
                // show text
                $('#medication-name-' + index).toggle();
                $('#medication-sig-' + index).toggle();

                // hide textarea
                $('#medication-edit-' + index).toggle();
                $('#medication-edit-sig-' + index).toggle();

                // show all edit buttons
                $('.medication-edit-btn').show();
                $('.medication-delete-btn').show();

                // hide save button
                $('#medication-save-btn-' + index).toggle();
            }, function (response) {
                console.log(response);
            });
        },

        deleteMedication: function(index, e) {
            if(confirm("Are you sure you want to delete this medication?")) {
                var payload = {
                    'medication': this.medications[index]
                };

                this.$http.post('/CCDModels/Items/MedicationListItem/destroy', payload).then(function (response) {
                    Vue.delete(this.medications, index);
                }, function (response) {
                    console.log(response);
                });
            }

            return false;
        },

        postEvents: function(index, e) {
            this.$http.post('/CCDModels/Items/MedicationListItem/store', this.medications).then(function (response) {
            }, function (response) {
                console.log(response);
            });
        }
    }
});

