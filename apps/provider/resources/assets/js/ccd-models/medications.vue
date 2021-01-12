<script>
    export default {
        data() {
            return {
                medication: {
                    id: '',
                    patient_id: $('meta[name="patient_id"]').attr('content'),
                    name: '',
                    sig: ''
                },
                medications: [],
                patientId: $('meta[name="patient_id"]').attr('content'),
            }
        },

        mounted: function () {
            this.loadMedications();
        },

        methods: {
            loadMedications: function () {
                let self = this
                let params = {
                    params: {
                        patient_id: this.patientId
                    }
                };

                this.axios.get('/CCDModels/Items/MedicationListItem', params).then(function (response) {
                    self.medications = response.data;
                }, function (response) {
                    console.log(response);
                });
            },

            addMedication: function () {
                if (this.medication.name) {
                    let self = this

                    let payload = {
                        'medication': this.medication
                    };

                    this.axios.post('/CCDModels/Items/MedicationListItem/store', payload).then(function (response) {
                        let id = response.data.id.id;

                        self.medications.push({
                            id: id,
                            patient_id: self.patientId,
                            name: response.data.id.name,
                            sig: response.data.id.sig
                        });
                        self.medication = {id: '', patient_id: self.patientId, name: '', sig: ''};
                    }, function (response) {
                        console.log(response);
                    });
                }
            },

            editMedication: function (index) {
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

            updateMedication: function (index) {
                var payload = {
                    'medication': this.medications[index]
                };

                this.axios.post('/CCDModels/Items/MedicationListItem/update', payload).then(function (response) {
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

            deleteMedication: function (index, e) {
                if (confirm("Are you sure you want to delete this medication?")) {
                    let self = this;
                    let payload = {
                        'medication': this.medications[index]
                    };

                    this.axios.post('/CCDModels/Items/MedicationListItem/destroy', payload).then(function (response) {
                        self.medications.splice(index, 1);
                    }, function (response) {
                        console.log(response);
                    });
                }
            },

            postEvents: function (index, e) {
                this.axios.post('/CCDModels/Items/MedicationListItem/store', this.medications).then(function (response) {
                }, function (response) {
                    console.log(response);
                });
            }
        }

    }


</script>

<template>
    <div class="row" id="medications">
        <div class="col-sm-12">
            <div class="list-group">
                <template>
                    <div v-for="(medicationitem, index) in medications" :key="index">
                        <div class="list-group-item" v-on:submit.prevent v-if="medicationitem.name || medicationitem.sig"
                         style="padding:5px;font-size:12px;">
                        <div class="row">
                            <div class="col-sm-10">
                                <div class="list-group-item-heading">
                                    <span :id="'medication-name-' + index">
                                        <strong>{{ medicationitem.name}}</strong>
                                    </span>

                                    <span :id="'medication-sig-'+index"><br/>{{ medicationitem.sig }}</span>

                                    <textarea v-model="medicationitem.name" :id="'medication-edit-'+index"
                                              style="display:none;" rows="5"></textarea>
                                    <textarea v-model="medicationitem.sig" :id="'medication-edit-sig-'+index"
                                              style="display:none;" rows="5"></textarea>
                                    <input type="hidden" name="id" :value="'medicationitem.id'">
                                    <input type="hidden" name="patient_id" :value="medicationitem.patient_id">
                                </div>
                            </div>
                            <div class="col-sm-2 text-right">

                                <p class="list-group-item-text">{{ medicationitem.description }}</p>
                                <button class="btn btn-xs btn-danger medication-delete-btn"
                                        v-on:click.stop.prevent="deleteMedication(index, medication)"><span><i
                                        class="glyphicon glyphicon-remove"></i></span>
                                </button>

                                <button class="btn btn-xs btn-primary medication-edit-btn"
                                        v-on:click.stop.prevent="editMedication(index, medication)"><span><i
                                        class="glyphicon glyphicon-pencil"></i></span>
                                </button>
                                <button class="btn btn-xs btn-success medication-save-btn"
                                        :id="'medication-save-btn-'+index"
                                        v-on:click.stop.prevent="updateMedication(index, medication)"
                                        style="display:none;"><span><i class="glyphicon glyphicon-ok"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    </div>
                </template>

            </div>
        </div>


        <!-- add an medication form -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Add a Medication
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-9">
                            <input type="hidden" id="patient_id" name="patient_id" :value="patientId">
                            <div class="form-group">
                                <input class="form-control" placeholder="Medication Name" v-model="medication.name">
                                <input class="form-control" placeholder="Instructions" v-model="medication.sig">
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <button class="btn btn-success" v-on:click.stop.prevent="addMedication()"><span><i
                                    class="glyphicon glyphicon-plus"></i> Add</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>