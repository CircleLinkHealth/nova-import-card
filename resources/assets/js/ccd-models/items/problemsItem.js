var Vue = require('vue');

Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

var patientId = $('#patient_id').val();

new Vue({
    el: '#problems',
    data: {
        // default form values
        problem: { id: '', patient_id: patientId, name: '' }
    },
    // Anything within the ready function will run when the application loads
    ready: function() {
        // When the application loads, we want to call the method that initializes
        // some data
        this.loadProblems();
    },
    // Methods we want to use in our application are registered here
    methods: {

        loadProblems: function() {
            var patientId = $('#patient_id').val();
            // GET request
            this.$http({url: '/CCDModels/Items/ProblemsItem', method: 'GET', params: {'patient_id': patientId }}).then((response) => {
                // success callback
                this.$set('problems', response.data);
            }, (response) => {
                // error callback
            }
            )
        },

        // Adds an problem to the existing problems array
        addProblem: function() {

            if(this.problem.name) {
                // add to array
                console.log(this.problem.name);

                // save on server
                // GET request
                this.$http.post('/CCDModels/Items/ProblemsItem/store', {'problem': this.problem}).then((response) => {
                    // log
                    console.log('new ccd_problem.id = ' + response.data.id.id);
                    // reset form values
                    var id = response.data.id.id;
                    var patient_id = $('#patient_id').val();
                    this.problems.push({ id: id, patient_id: patient_id, name: response.data.id.name });
                    this.problem = { id: '', patient_id: patient_id, name: '' };

                }, (response) => {

                    // error callback
                }
            )
            };
        },

        // Edit an existing problemon the array
        editProblem: function(index) {
            // hide text
            $('#problem-name-' + index).toggle();

            // show textarea
            $('#problem-edit-' + index).toggle();

            // hide all edit buttons
            $('.problem-edit-btn').hide();
            $('.problem-delete-btn').hide();

            // show save button
            $('#problem-save-btn-' + index).toggle();
        },

        // Adds an problem to the existing problems array
        updateProblem: function(index) {

            this.$http.post('/CCDModels/Items/ProblemsItem/update', {'problem': this.problems[index]}).then((response) = > {
                // Put the results in a div

                // show text
                $('#problem-name-' + index).toggle();

            // hide textarea
            $('#problem-edit-' + index).toggle();

            // show all edit buttons
            $('.problem-edit-btn').show();
            $('.problem-delete-btn').show();

            // hide save button
            $('#problem-save-btn-' + index).toggle();

        },
            (response) =
            >
            {

            }
        },

        deleteProblem: function(index, e) {
            //e.preventDefault();
            //e.stopPropagation();
            if(confirm("Are you sure you want to delete this problem?")) {
                var thisMed = this.problems[0];

                var posting = this.$http.post("/CCDModels/Items/ProblemsItem/destroy", {'problem': this.problems[index]});
                // delete from vue array
                Vue.delete(this.problems, index);
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

            var posting = $.post( "/CCDModels/Items/ProblemsItem/store", this.problems );
            console.log(this.problems);
            // Put the results in a div
            posting.done(function( data ) {
                console.log(data);
                // hide all textareas

                // show edit buttons
            });
        },
    }
});;;;


