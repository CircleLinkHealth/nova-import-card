var Vue = require('vue');

Vue.use(require('vue-resource'));

new Vue({
    el: '#medications',
    data: {
        // default form values
        medication: { name: '', description: '', date: '' }
    },
    // Anything within the ready function will run when the application loads
    ready: function() {
        // When the application loads, we want to call the method that initializes
        // some data
        this.fetchEvents();
        this.loadUsers();
    },
    // Methods we want to use in our application are registered here
    methods: {

        loadUsers: function() {
            this.$http.get('/ajax/get', function(data, status, request){
                console.log('STATUS: ' + status);
                if(status == 200)
                {
                    this.medications = data;
                }
            });
        },

        // We dedicate a method to retrieving and setting some data
        fetchEvents: function() {
            var medications = [
                {
                    id: 1,
                    name: 'Default',
                    //description: 'Toronto International Film Festival',
                    //date: '2015-09-10'
                }
            ];
            // $set is a convenience method provided by Vue that is similar to pushing
            // data onto an array
            this.$set('medications', medications);
        },

        // Adds an medication to the existing medications array
        addEvent: function() {
            if(this.medication.name) {
                // add to array
                this.medications.push(this.medication);

                // reset form values
                this.medication = { name: '', description: '', date: '' };

                // save on server
                this.postEvents();
            }
        },

        // Edit an existing medicationon the array
        editEvent: function(index) {
            // hide text
            $('#medication-name-' + index).toggle();

            // show textarea
            $('#medication-edit-' + index).toggle();

            // hide all edit buttons
            $('.medication-edit-btn').hide();
            $('.medication-delete-btn').hide();

            // show save button
            $('#medication-save-btn-' + index).toggle();
        },

        // Adds an medication to the existing medications array
        storeEvent: function(index) {
            // show text
            $('#medication-name-' + index).toggle();

            // hide textarea
            $('#medication-edit-' + index).toggle();

            // show all edit buttons
            $('.medication-edit-btn').show();
            $('.medication-delete-btn').show();

            // hide save button
            $('#medication-save-btn-' + index).toggle();

            // save on server
            this.postEvents();
        },

        deleteEvent: function(index, e) {
            //e.preventDefault();
            //e.stopPropagation();
            if(confirm("Are you sure you want to delete this medication?")) {
                // $remove is a Vue convenience method similar to splice
                console.log(this.medications);
                Vue.delete(this.medications, index);
                //this.medications.splice(index, 1);
                console.log(this.medications);
                // save on server
                this.postEvents();
                return false;
            }
            return false;
        },

        postEvents: function(index, e) {

            // Send the data using post
            var posting = $.post( "/ajax/post", this.medications );
            console.log(this.medications);
            // Put the results in a div
            posting.done(function( data ) {
                console.log(data);
                // hide all textareas

                // show edit buttons
            });
        }
    }
});

