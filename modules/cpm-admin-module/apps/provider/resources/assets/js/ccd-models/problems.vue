<script>
    export default {
        data() {
            return {
                problem: {
                    id: '',
                    patient_id: $('meta[name="patient_id"]').attr('content'),
                    name: ''
                },
                problems: []
            }
        },

        mounted: function () {
            this.loadProblems();
        },

        methods: {
            loadProblems: function () {
                let self = this

                let params = {
                    params: {
                        patient_id: self.problem.patient_id
                    }
                };

                this.axios.get('/CCDModels/Items/ProblemsItem', params).then(function (response) {
                    self.problems = response.data;
                }, function (response) {
                    console.log(response);
                });
            },

            addProblem: function () {
                if (this.problem.name) {
                    let self = this

                    let payload = {
                        'problem': this.problem
                    };

                    this.axios.post('/CCDModels/Items/ProblemsItem/store', payload).then(function (response) {
                        let id = response.data.id.id;
                        
                        self.problems.push({id: id, patient_id: self.problem.patient_id, name: response.data.id.name});
                        self.problem = {id: '', patient_id: self.problem.patient_id, name: ''};
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
                let payload = {
                    'problem': this.problems[index]
                };

                this.axios.post('/CCDModels/Items/ProblemsItem/update', payload).then(function (response) {
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
                    let self = this

                    let payload = {
                        'problem': this.problems[index]
                    };

                    this.axios.post('/CCDModels/Items/ProblemsItem/destroy', payload).then(function (response) {
                        self.problems.splice(index, 1);
                    }, function (response) {
                        console.log(response);
                    });
                }
            },

            postEvents: function (index, e) {
                this.axios.post('/CCDModels/Items/ProblemsItem/store', this.problems).then(function (response) {
                }, function (response) {
                    console.log(response);
                });
            }
        }
    }
</script>


<template>
    <div class="row" id="problems">
        <div class="col-sm-12">
            <div class="list-group">
                <template>
                    <div v-for="(problemitem, index) in problems" :key="index">
                        <div class="list-group-item" v-on:submit.prevent v-if="problemitem.name"
                         style="padding:5px;font-size:12px;">
                            <div class="row">
                                <div class="col-sm-10">
                                    <div class="list-group-item-heading">
                                        <span :id="'problem-name-'+index">{{ problemitem.name }}</span>
                                        <textarea v-model="problemitem.name" :id="'problem-edit-'+index" style="display:none;" rows="10"></textarea>
                                        <input type="hidden" name="id" :value="problemitem.id">
                                        <input type="hidden" name="patient_id" :value="problemitem.patient_id">
                                    </div>
                                </div>
                                <div class="col-sm-2 text-right">

                                    <p class="list-group-item-text" v-if="problemitem.name">{{ problemitem.description }}</p>
                                    <button class="btn btn-xs btn-danger problem-delete-btn" v-if="problemitem.name" v-on:click.stop.prevent="deleteProblem(index, problem)" ><span><i class="glyphicon glyphicon-remove"></i></span></button>
                                    <button class="btn btn-xs btn-primary problem-edit-btn" v-if="problemitem.name" v-on:click.stop.prevent="editProblem(index, problem)"><span><i class="glyphicon glyphicon-pencil"></i></span></button>
                                    <button class="btn btn-xs btn-success problem-save-btn" :id="'problem-save-btn-'+index" v-if="problemitem.name" v-on:click.stop.prevent="updateProblem(index, problem)" style="display:none;"><span><i class="glyphicon glyphicon-ok"></i></span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </div>


        <!-- add an problem form -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Add a Problem
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-9">
                            <input type="hidden" id="patient_id" name="patient_id" :value="problem.patient_id">
                            <div class="form-group">
                                <input class="form-control" placeholder="Problem Name" v-model="problem.name">
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <button class="btn btn-success" v-on:click.stop.prevent="addProblem()"><span><i class="glyphicon glyphicon-plus"></i> Add</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>