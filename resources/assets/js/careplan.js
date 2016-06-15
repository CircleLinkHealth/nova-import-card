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
    },
    // Methods we want to use in our application are registered here
    methods: {

        // We dedicate a method to retrieving and setting some data
        fetchEvents: function() {
            var medications = [
                {
                    id: 1,
                    name: 'Example Medication 1',
                    //description: 'Toronto International Film Festival',
                    //date: '2015-09-10'
                }
            ];
            // $set is a convenience method provided by Vue that is similar to pushing
            // data onto an array
            this.$set('medications', medications);
        },

        // ajax success
        onSuccess: function(data, status, xhr) {
                // with our success handler, we're just logging the data...
                console.log(data, status, xhr);
                // but you can do something with it if you like - the JSON is deserialised into an object
                console.log(String(data.value).toUpperCase())
        },

        // Adds an medication to the existing medications array
        addEvent: function() {
            if(this.medication.name) {
                this.medications.push(this.medication);
                this.medication = { name: '', description: '', date: '' };
            }
            // we're not passing any data with the get route, though you can if you want
            $.get( "/ajax/get", function( data ) {

                alert( "Data Loaded: " + data );
            });

            // Send the data using post
            var posting = $.post( "/ajax/post", this.medications );

            // Put the results in a div
            posting.done(function( data ) {
                alert('posted!');
                console.log(data);
                // hide all textareas

                // show edit buttons
            });
        },

        // Adds an medication to the existing medications array
        editEvent: function(index) {
            // show textarea
            var editId = 'medication-edit-' + index;
            $('#' + editId).toggle();
            //alert(editId);

            // hide all edit buttons

            // show save button
        },

        // Adds an medication to the existing medications array
        storeEvent: function(index) {

            // hide textarea
            var editId = 'medication-edit-' + index;
            $('#' + editId).toggle();

            // show all edit buttons

            // hide save button
        },

        deleteEvent: function(index, e) {
            //e.preventDefault();
            //e.stopPropagation();
            if(confirm("Are you sure you want to delete this medication?")) {
                // $remove is a Vue convenience method similar to splice
                //alert(index);
                console.log(this.medications);
                //this.medications.$remove(index);
                /*
                this.medications.push({ id: 5,
                    name: 'The Martian Premiere',
                    description: 'The Martian comes to theatres.',
                    date: '2015-10-02' });
                    */
                Vue.delete(this.medications, index);
                //this.medications.splice(index, 1);
                console.log(this.medications);
                //alert('done');
                return false;
            }
            return false;
        }
    }
});

