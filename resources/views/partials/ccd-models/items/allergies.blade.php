    <!-- main body of our application -->
    <div class="row" id="allergies">

        <!-- show the allergies -->
        <div class="col-sm-12">
            <div class="list-group">
                <template v-for="allergyitem in allergies">
                <div href="#" class="list-group-item" v-on:submit.prevent v-if="allergyitem.name" style="padding:5px;font-size:12px;">
                    <div class="row">
                        <div class="col-sm-9">
                            <div class="list-group-item-heading" v-if="allergyitem.name">
                                <span id="allergy-name-@{{ $index }}">@{{ allergyitem.name }}</span>
                                <textarea v-model="allergyitem.name" id="allergy-edit-@{{ $index }}" style="display:none;" rows="10">@{{ allergyitem.name }}</textarea>
                                <input type="hidden" name="id" value="@{{ allergyitem.id }}">
                                <input type="hidden" name="patient_id" value="@{{ allergyitem.patient_id }}">
                            </div>
                        </div>
                        <div class="col-sm-3 text-right">

                            <p class="list-group-item-text" v-if="allergyitem.name">@{{ allergyitem.description }}</p>
                            <button class="btn btn-xs btn-danger allergy-delete-btn" v-if="allergyitem.name" v-on:click.stop.prevent="deleteAllergy($index, $allergy)" ><span><i class="glyphicon glyphicon-remove"></i></span></button>
                            <button class="btn btn-xs btn-primary allergy-edit-btn" v-if="allergyitem.name" v-on:click.stop.prevent="editAllergy($index, $allergy)"><span><i class="glyphicon glyphicon-pencil"></i></span></button>
                            <button class="btn btn-xs btn-success allergy-save-btn" id="allergy-save-btn-@{{ $index }}" v-if="allergyitem.name" v-on:click.stop.prevent="updateAllergy($index, $allergy)" style="display:none;"><span><i class="glyphicon glyphicon-ok"></i></span></button>
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
                            <input type="hidden" id="patient_id" name="patient_id" value="{{ $patient->id }}">
                            <div class="form-group">
                                <input class="form-control" placeholder="Allergy Name" v-model="allergy.name">
                            </div>
                        </div>
                        <div class="col-sm-2 text-right">
                            <button class="btn btn-success" v-on:click.stop.prevent="addAllergy()"><span><i class="glyphicon glyphicon-plus"></i> Add</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>



    <!-- JS -->
    <script src="/js/allergiesItem.js"></script>