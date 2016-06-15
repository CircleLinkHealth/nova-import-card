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
                    </h4>

                    <h5>
                        <i class="glyphicon glyphicon-calendar" v-if="medicationitem.date"></i>
                        @{{ medicationitem.date }}
                    </h5>

                    <p class="list-group-item-text" v-if="medicationitem.name">@{{ medicationitem.description }}</p>
                    <button class="btn btn-xs btn-danger medication-delete-btn" v-if="medicationitem.name" v-on:click.stop.prevent="deleteEvent($index, $medication)" >Delete</button>
                    <button class="btn btn-xs btn-primary medication-edit-btn" v-if="medicationitem.name" v-on:click.stop.prevent="editEvent($index, $medication)">Edit</button>
                    <button class="btn btn-xs btn-success medication-save-btn" id="medication-save-btn-@{{ $index }}" v-if="medicationitem.name" v-on:click.stop.prevent="storeEvent($index, $medication)" style="display:none;">Save</button>
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

                    <div class="form-group">
                        <input class="form-control" placeholder="Medication Name" v-model="medication.name">
                    </div>

                    <button class="btn btn-primary" v-on:click.stop.prevent="addEvent()">Submit</button>

                </div>
            </div>
        </div>

    </div>



    <!-- JS -->
    <script src="/js/medicationItem.js"></script>