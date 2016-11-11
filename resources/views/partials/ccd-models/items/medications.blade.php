    <!-- main body of our application -->
    <div class="row" id="medications">

        <!-- show the medications -->
        <div class="col-sm-12">
            <div class="list-group">
                <template v-for="medicationitem in medications">
                    <div class="list-group-item" v-on:submit.prevent v-if="medicationitem.name && medicationitem.sig"
                         style="padding:5px;font-size:12px;">
                    <div class="row">
                        <div class="col-sm-10">
                            <div class="list-group-item-heading">
                                <span id="medication-name-@{{ $index }}"><strong>@{{ medicationitem.name }}</strong></span>
                                <span id="medication-sig-@{{ $index }}"><br />@{{ medicationitem.sig }}</span>
                                <textarea v-model="medicationitem.name" id="medication-edit-@{{ $index }}" style="display:none;" rows="5">@{{ medicationitem.name }}</textarea>
                                <textarea v-model="medicationitem.sig" id="medication-edit-sig-@{{ $index }}" style="display:none;" rows="5">@{{ medicationitem.sig }}</textarea>
                                <input type="hidden" name="id" value="@{{ medicationitem.id }}">
                                <input type="hidden" name="patient_id" value="@{{ medicationitem.patient_id }}">
                            </div>
                        </div>
                        <div class="col-sm-2 text-right">

                            <p class="list-group-item-text">@{{ medicationitem.description }}</p>
                            <button class="btn btn-xs btn-danger medication-delete-btn" v-on:click.stop.prevent="deleteMedication($index, $medication)" ><span><i class="glyphicon glyphicon-remove"></i></span></button>
                            <button class="btn btn-xs btn-primary medication-edit-btn" v-on:click.stop.prevent="editMedication($index, $medication)"><span><i class="glyphicon glyphicon-pencil"></i></span></button>
                            <button class="btn btn-xs btn-success medication-save-btn" id="medication-save-btn-@{{ $index }}" v-on:click.stop.prevent="updateMedication($index, $medication)" style="display:none;"><span><i class="glyphicon glyphicon-ok"></i></span></button>
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
                            <input type="hidden" id="patient_id" name="patient_id" value="{{ $patient->id }}">
                            <div class="form-group">
                                <input class="form-control" placeholder="Medication Name" v-model="medication.name">
                                <input class="form-control" placeholder="Instructions" v-model="medication.sig">
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">
                            <button class="btn btn-success" v-on:click.stop.prevent="addMedication()"><span><i class="glyphicon glyphicon-plus"></i> Add</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>



    <!-- JS -->
    <script src="/js/medicationItem.js"></script>