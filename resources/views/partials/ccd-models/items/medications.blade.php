    <!-- main body of our application -->
    <div class="row" id="medications">

        <!-- show the medications -->
        <div class="col-sm-12">
            <div class="list-group">
                <template v-for="medicationitem in medications">
                <div href="#" class="list-group-item" v-on:submit.prevent v-if="medicationitem.name">
                    <h4 class="list-group-item-heading" v-if="medicationitem.name">
                        <span id="medication-name-@{{ $index }}"><i class="glyphicon glyphicon-asterisk"></i> @{{ medicationitem.name }}</span>
                        <textarea v-model="medicationitem.name" id="medication-edit-@{{ $index }}" style="display:none;">@{{ medicationitem.name }}</textarea>
                        <input type="hidden" name="id" value="@{{ medicationitem.id }}">
                        <input type="hidden" name="patient_id" value="@{{ medicationitem.patient_id }}">
                    </h4>

                    <p class="list-group-item-text" v-if="medicationitem.name">@{{ medicationitem.description }}</p>
                    <button class="btn btn-xs btn-danger medication-delete-btn" v-if="medicationitem.name" v-on:click.stop.prevent="deleteMedication($index, $medication)" >Delete</button>
                    <button class="btn btn-xs btn-primary medication-edit-btn" v-if="medicationitem.name" v-on:click.stop.prevent="editMedication($index, $medication)">Edit</button>
                    <button class="btn btn-xs btn-success medication-save-btn" id="medication-save-btn-@{{ $index }}" v-if="medicationitem.name" v-on:click.stop.prevent="updateMedication($index, $medication)" style="display:none;">Save</button>
                </div>
                </template>

            </div>
        </div>


        <!-- add an medication form -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3>Add a Medication</h3>
                </div>
                <div class="panel-body">

                    <input type="hidden" id="patient_id" name="patient_id" value="{{ $patient->ID }}">
                    <div class="form-group">
                        <input class="form-control" placeholder="Medication Name" v-model="medication.name">
                    </div>

                    <button class="btn btn-primary" v-on:click.stop.prevent="addMedication()">Submit</button>

                </div>
            </div>
        </div>

    </div>



    <!-- JS -->
    <script src="/js/medicationItem.js"></script>