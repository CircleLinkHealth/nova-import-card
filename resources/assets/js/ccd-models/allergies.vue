<script>
    export default {
        data() {
            return {
                allergy: {
                    id: '',
                    patient_id: $('meta[name="patient_id"]').attr('content'),
                    name: ''
                },
                allergies: []
            }
        },

        mounted: function () {
            this.loadAllergies();
        },

        methods: {
            loadAllergies: function () {
                let self = this

                let params = {
                    params: {
                        patient_id: self.allergy.patient_id
                    }
                };

                this.axios.get('/CCDModels/Items/AllergiesItem', params).then(function (response) {
                    self.allergies = response.data;
                }, function (response) {
                    console.log(response);
                });
            },

            addAllergy: function () {

                if (this.allergy.name) {
                    let self = this

                    let payload = {
                        'allergy': this.allergy
                    };

                    this.axios.post('/CCDModels/Items/AllergiesItem/store', payload).then(function (response) {
                        let id = response.data.id.id;

                        self.allergies.push({
                            id: id,
                            patient_id: self.allergy.patient_id,
                            name: response.data.id.allergen_name
                        });

                        //reset new allergy
                        self.allergy = {
                            id: '',
                            patient_id: self.allergy.patient_id,
                            name: ''
                        };

                    }, function (response) {
                        console.log(response);
                    });

                }
            },

            editAllergy: function (index) {
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

            updateAllergy: function (index) {
                let payload = {
                    allergy: this.allergies[index]
                };

                this.axios.post('/CCDModels/Items/AllergiesItem/update', payload).then(function (response) {
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

            deleteAllergy: function (index, e) {
                if (confirm("Are you sure you want to delete this allergy?")) {
                    let self = this

                    let payload = {
                        'allergy': self.allergies[index]
                    };

                    this.axios.post('/CCDModels/Items/AllergiesItem/destroy', payload).then(function (response) {
                        self.allergies.splice(index, 1);
                    }, function (response) {
                        console.log(response);
                    });
                }
            },

            postEvents: function (index, e) {
                this.axios.post('/CCDModels/Items/AllergiesItem/store', this.allergies).then(function (response) {
                }, function (response) {
                    console.log(response);
                });
            }
        }
    }
</script>


<template>
    <div class="row" id="allergies">
        <!-- show the allergies -->
        <div class="col-sm-12">
            <div class="list-group">
                <template>
                    <div v-for="(allergyitem, index) in allergies" :key="index">
                        <div href="#" class="list-group-item" v-on:submit.prevent v-if="allergyitem.name"
                            style="padding:5px;font-size:12px;">
                            <div class="row">
                                <div class="col-sm-9">
                                    <div class="list-group-item-heading" v-if="allergyitem.name">
                                        <span :id="'allergy-name-'+index">{{ allergyitem.name }}</span>
                                        <textarea v-model="allergyitem.name" :id="'allergy-edit-'+index"
                                                style="display:none;" rows="10">{{ allergyitem.name }}</textarea>
                                        <input type="hidden" name="id" :value="allergyitem.id">
                                        <input type="hidden" name="patient_id" :value="allergyitem.patient_id">
                                    </div>
                                </div>
                                <div class="col-sm-3 text-right">

                                    <p class="list-group-item-text" v-if="allergyitem.name">
                                        {{ allergyitem.description }}
                                    </p>
                                    <button class="btn btn-xs btn-danger allergy-delete-btn" v-if="allergyitem.name"
                                            v-on:click.stop.prevent="deleteAllergy(index, allergy)"><span><i
                                            class="glyphicon glyphicon-remove"></i></span>
                                    </button>
                                    <button class="btn btn-xs btn-primary allergy-edit-btn" v-if="allergyitem.name"
                                            v-on:click.stop.prevent="editAllergy(index, allergy)"><span><i
                                            class="glyphicon glyphicon-pencil"></i></span>
                                    </button>
                                    <button class="btn btn-xs btn-success allergy-save-btn"
                                            :id="'allergy-save-btn-'+index" v-if="allergyitem.name"
                                            v-on:click.stop.prevent="updateAllergy(index, allergy)" style="display:none;">
                                        <span><i class="glyphicon glyphicon-ok"></i></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>


        <!-- add an allergy form -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Add an Allergy
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-10">
                            <input type="hidden" id="patient_id" name="patient_id" :value="allergy.patient_id">
                            <div class="form-group">
                                <input class="form-control" placeholder="Allergy Name" v-model="allergy.name">
                            </div>
                        </div>
                        <div class="col-sm-2 text-right">
                            <button class="btn btn-success" v-on:click.stop.prevent="addAllergy()"><span><i
                                    class="glyphicon glyphicon-plus"></i> Add</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>