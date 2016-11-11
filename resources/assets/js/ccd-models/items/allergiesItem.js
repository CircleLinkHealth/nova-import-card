var Vue = require('vue');

Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

var patientId = $('#patient_id').val();

var allergiesVM = new Vue({
    el: '#allergies',
    data: {
        allergy: {
            id: '',
            patient_id: patientId,
            name: ''
        },
        allergies: []
    },

    ready: function() {
        this.loadAllergies();
    },

    methods: {

        loadAllergies: function () {
            var params = {
                'patient_id': $('#patient_id').val()
            };

            this.$http.get('/CCDModels/Items/AllergiesItem', params).then(function (response) {
                allergiesVM.allergies = response.data;
            }, function (response) {
                console.log(response);
            });
        },

        addAllergy: function() {

            if(this.allergy.name) {
                var payload = {
                    'allergy': this.allergy
                };

                this.$http.post('/CCDModels/Items/AllergiesItem/store', payload).then(function (response) {
                    var id = response.data.id.id;
                    var patient_id = $('#patient_id').val();

                    allergiesVM.allergies.push({
                        id: id,
                        patient_id: patient_id,
                        name: response.data.id.allergen_name
                    });

                    //reset new allergy
                    allergiesVM.allergy = {
                        id: '',
                        patient_id: patient_id,
                        name: ''
                    };

                }, function (response) {
                    console.log(response);
                });

            }
        },

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

        updateAllergy: function(index) {
            var payload = {
                'allergy': this.allergies[index]
            };

            this.$http.post('/CCDModels/Items/AllergiesItem/update', payload).then(function (response) {
                // show text
                $('#allergy-name-' + index).toggle();

                // hide textarea
                $('#allergy-edit-' + index).toggle();

                // show all edit buttons
                $('.allergy-edit-btn').show();
                $('.allergy-delete-btn').show();

                // hide save button
                $('#allergy-save-btn-' + index).toggle();
            }, function (response) {
                console.log(response);
            });
        },

        deleteAllergy: function(index, e) {
            if(confirm("Are you sure you want to delete this allergy?")) {
                var payload = {
                    'allergy': allergiesVM.allergies[index]
                };

                this.$http.post('/CCDModels/Items/AllergiesItem/destroy', payload).then(function (response) {
                    Vue.delete(allergiesVM.allergies, index);
                }, function (response) {
                    console.log(response);
                });
            }
        },

        postEvents: function(index, e) {
            this.$http.post('/CCDModels/Items/AllergiesItem/store', this.allergies).then(function (response) {
            }, function (response) {
                console.log(response);
            });
        }
    }
});

