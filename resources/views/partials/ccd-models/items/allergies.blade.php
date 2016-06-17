    <!-- main body of our application -->
    <div class="row" id="allergies">

        <!-- show the allergies -->
        <div class="col-sm-12">
            <div class="list-group">
                <template v-for="allergyitem in allergies">
                <div href="#" class="list-group-item" v-on:submit.prevent v-if="allergyitem.name">
                    <h4 class="list-group-item-heading" v-if="allergyitem.name">
                        <span id="allergy-name-@{{ $index }}"><i class="glyphicon glyphicon-asterisk"></i> @{{ allergyitem.name }}</span>
                        <textarea v-model="allergyitem.name" id="allergy-edit-@{{ $index }}" style="display:none;">@{{ allergyitem.name }}</textarea>
                        <input type="hidden" name="id" value="@{{ allergyitem.id }}">
                        <input type="hidden" name="patient_id" value="@{{ allergyitem.patient_id }}">
                    </h4>

                    <p class="list-group-item-text" v-if="allergyitem.name">@{{ allergyitem.description }}</p>
                    <button class="btn btn-xs btn-danger allergy-delete-btn" v-if="allergyitem.name" v-on:click.stop.prevent="deleteAllergy($index, $allergy)" >Delete</button>
                    <button class="btn btn-xs btn-primary allergy-edit-btn" v-if="allergyitem.name" v-on:click.stop.prevent="editAllergy($index, $allergy)">Edit</button>
                    <button class="btn btn-xs btn-success allergy-save-btn" id="allergy-save-btn-@{{ $index }}" v-if="allergyitem.name" v-on:click.stop.prevent="updateAllergy($index, $allergy)" style="display:none;">Save</button>
                </div>
                </template>

            </div>
        </div>


        <!-- add an allergy form -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3>Add a Allergy</h3>
                </div>
                <div class="panel-body">

                    <input type="hidden" id="patient_id" name="patient_id" value="{{ $patient->ID }}">
                    <div class="form-group">
                        <input class="form-control" placeholder="Allergy Name" v-model="allergy.name">
                    </div>

                    <button class="btn btn-primary" v-on:click.stop.prevent="addAllergy()">Submit</button>

                </div>
            </div>
        </div>

    </div>



    <!-- JS -->
    <script src="/js/allergiesItem.js"></script>