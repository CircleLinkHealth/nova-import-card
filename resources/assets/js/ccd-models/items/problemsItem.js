var Vue = require('vue');

Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

var patientId = $('#patient_id').val();

new Vue({
    el: '#problems',
    data: {
        problem: {
            id: '',
            patient_id: patientId,
            name: ''
        }
    },

    ready: function () {
        this.loadProblems();
    },

    methods: {
        loadProblems: function () {
            var payload = {
                'patient_id': $('#patient_id').val()
            };

            this.$http.get('/CCDModels/Items/ProblemsItem', {params: payload}).then(function (response) {
                this.$set('problems', response.data);
            }, function (response) {
                console.log(response);
            });
        },

        addProblem: function () {
            if (this.problem.name) {
                var payload = {
                    'problem': this.problem
                };

                this.$http.post('/CCDModels/Items/ProblemsItem/store', payload).then(function (response) {
                    var id = response.data.id.id;
                    var patient_id = $('#patient_id').val();
                    this.problems.push({id: id, patient_id: patient_id, name: response.data.id.name});
                    this.problem = {id: '', patient_id: patient_id, name: ''};
                }, function (response) {
                    console.log(response);
                });
            }
        },

        editProblem: function (index) {
            $('#problem-name-' + index).toggle();
            $('#problem-edit-' + index).toggle();
            $('.problem-edit-btn').hide();
            $('.problem-delete-btn').hide();
            $('#problem-save-btn-' + index).toggle();
        },

        updateProblem: function (index) {
            var payload = {
                'problem': this.problems[index]
            };

            this.$http.post('/CCDModels/Items/ProblemsItem/update', payload).then(function (response) {
                $('#problem-name-' + index).toggle();
                $('#problem-edit-' + index).toggle();
                $('.problem-edit-btn').show();
                $('.problem-delete-btn').show();
                $('#problem-save-btn-' + index).toggle();
            }, function (response) {
                console.log(response);
            });
        },

        deleteProblem: function (index, e) {
            if (confirm("Are you sure you want to delete this problem?")) {

                var payload = {
                    'problem': this.problems[index]
                };

                this.$http.post('/CCDModels/Items/ProblemsItem/destroy', payload).then(function (response) {
                    Vue.delete(this.problems, index);
                }, function (response) {
                    console.log(response);
                });
            }
        },

        postEvents: function (index, e) {
            this.$http.post('/CCDModels/Items/ProblemsItem/store', this.problems).then(function (response) {
            }, function (response) {
                console.log(response);
            });
        }
    }
});



