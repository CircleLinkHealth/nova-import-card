var Vue = require('vue');

Vue.use(require('vue-resource'));

var patientId = $('#patient_id').val();

new Vue({
    el: '#allergies',
    data: {
        // default form values
        allergy: { id: '', patient_id: patientId, name: '' }
    },
    // Anything within the ready function will run when the application loads
    ready: function() {
        // When the application loads, we want to call the method that initializes
        // some data
        this.loadAllergys();
    },
    // Methods we want to use in our application are registered here
    methods: {

        loadAllergys: function() {
            var patientId = $('#patient_id').val();
            // GET request
            this.$http({url: '/CCDModels/Items/AllergiesItem', method: 'GET', params: {'patient_id': patientId }}).then((response) => {
                // success callback
                this.$set('allergies', response.data);
            }, (response) => {
                // error callback
            });
        },

        // Adds an allergy to the existing allergies array
        addAllergy: function() {

            if(this.allergy.name) {
                // add to array
                console.log(this.allergy.name);

                // save on server
                // GET request
                this.$http.post('/CCDModels/Items/AllergiesItem/store', {'allergy': this.allergy}).then((response) => {
                    // log
                    console.log('new ccd_allergy.id = ' + response.data.id.id);
                    // reset form values
                    var id = response.data.id.id;
                    var patient_id = $('#patient_id').val();
                    this.allergies.push({ id: id, patient_id: patient_id, name: response.data.id.allergen_name });
                    console.log(this.allergies);

                    this.allergy = { id: '', patient_id: patient_id, name: '' };

                }, (response) => {

                    // error callback
                });
            }
        },

        // Edit an existing allergy on the array
        editAllergy: function(index) {
            // hide text
            $('#allergy-name-' + index).toggle();

            // show textarea
            $('#allergy-edit-' + index).toggle();

            // hide all edit buttons
            $('.allergy-edit-btn').hide();
            $('.allergy-delete-btn').hide();

            // show save button
            $('#allergy-save-btn-' + index).toggle();
        },

        // Adds an allergy to the existing allergies array
        updateAllergy: function(index) {
            // save on server
            var posting = $.post( "/CCDModels/Items/AllergiesItem/update", {'allergy': this.allergies[index]} );
            console.log(this.allergies[index].name);
            // Put the results in a div
            posting.done(function( data ) {
                // log
                console.log(data);
                // show text
                $('#allergy-name-' + index).toggle();

                // hide textarea
                $('#allergy-edit-' + index).toggle();

                // show all edit buttons
                $('.allergy-edit-btn').show();
                $('.allergy-delete-btn').show();

                // hide save button
                $('#allergy-save-btn-' + index).toggle();
            });
        },

        deleteAllergy: function(index, e) {
            //e.preventDefault();
            //e.stopPropagation();
            if(confirm("Are you sure you want to delete this allergy?")) {
                var thisMed = this.allergies[0];
                // $remove is a Vue convenience method similar to splice
                console.log('All meds::' + this.allergies);
                console.log('This med id::' + this.allergies[index].id);
                console.log(this.allergies[index].name);
                // save on server
                var posting = $.post( "/CCDModels/Items/AllergiesItem/destroy", {'allergy': this.allergies[index]} );
                // delete from vue array
                Vue.delete(this.allergies, index);
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

            var posting = $.post( "/CCDModels/Items/AllergiesItem/store", this.allergies );
            console.log(this.allergies);
            // Put the results in a div
            posting.done(function( data ) {
                console.log(data);
                // hide all textareas

                // show edit buttons
            });
        },
    }
});

