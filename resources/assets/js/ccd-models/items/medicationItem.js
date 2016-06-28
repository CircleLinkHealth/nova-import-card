var Vue = require('vue');

Vue.use(require('vue-resource'));

var patientId = $('#patient_id').val();

new Vue({
    el: '#medications',
    data: {
        // default form values
        medication: { id: '', patient_id: patientId, name: '', sig: '' }
    },
    // Anything within the ready function will run when the application loads
    ready: function() {
        // When the application loads, we want to call the method that initializes
        // some data
        this.loadMedications();
    },
    // Methods we want to use in our application are registered here
    methods: {

        loadMedications: function() {
            var patientId = $('#patient_id').val();
            // GET request
            this.$http({url: '/CCDModels/Items/MedicationListItem', method: 'GET', params: {'patient_id': patientId }}).then((response) => {
                // success callback
                this.$set('medications', response.data);
            }, (response) => {
                // error callback
            });
        },

        // Adds an medication to the existing medications array
        addMedication: function() {

            if(this.medication.name) {
                // add to array
                console.log(this.medication.name);

                // save on server
                // GET request
                this.$http.post('/CCDModels/Items/MedicationListItem/store', {'medication': this.medication}).then((response) => {
                    // log
                    console.log('new ccd_medication.id = ' + response.data.id.id);
                    // reset form values
                    var id = response.data.id.id;
                    var patient_id = $('#patient_id').val();
                    this.medications.push({ id: id, patient_id: patient_id, name: response.data.id.name, sig: response.data.id.sig });
                    this.medication = { id: '', patient_id: patient_id, name: '', sig: '' };

                }, (response) => {

                    // error callback
                });
            }
        },

        // Edit an existing medicationon the array
        editMedication: function(index) {

            // hide text
            $('#medication-name-' + index).toggle();
            $('#medication-sig-' + index).toggle();

            // show textarea
            $('#medication-edit-' + index).toggle();
            $('#medication-edit-sig-' + index).toggle();

            // hide all edit buttons
            $('.medication-edit-btn').hide();
            $('.medication-delete-btn').hide();

            // show save button
            $('#medication-save-btn-' + index).toggle();
        },

        // Adds an medication to the existing medications array
        updateMedication: function(index) {
            // save on server
            var posting = $.post( "/CCDModels/Items/MedicationListItem/update", {'medication': this.medications[index]} );
            console.log(this.medications[index].name);
            // Put the results in a div
            posting.done(function( data ) {
                // log
                console.log(data);
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
            });
        },

        deleteMedication: function(index, e) {
            //e.preventDefault();
            //e.stopPropagation();
            if(confirm("Are you sure you want to delete this medication?")) {
                var thisMed = this.medications[0];
                // $remove is a Vue convenience method similar to splice
                console.log('All meds::' + this.medications);
                console.log('This med id::' + this.medications[index].id);
                console.log(this.medications[index].name);
                // save on server
                var posting = $.post( "/CCDModels/Items/MedicationListItem/destroy", {'medication': this.medications[index]} );
                // delete from vue array
                Vue.delete(this.medications, index);
                // Results
                posting.done(function( data ) {
                    console.log(data);
                });
                return false;
            }
            return false;
        },

        postEvents: function(index, e) {

            // Send the data using post

            var posting = $.post( "/CCDModels/Items/MedicationListItem/store", this.medications );
            console.log(this.medications);
            // Put the results in a div
            posting.done(function( data ) {
                console.log(data);
                // hide all textareas

                // show edit buttons
            });
        },
    }
});

