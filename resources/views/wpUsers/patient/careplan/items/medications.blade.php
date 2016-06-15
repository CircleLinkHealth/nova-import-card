    <!-- main body of our application -->
    <div class="row" id="medications">

        <!-- show the medications -->
        <div class="col-sm-12">
            <div class="list-group">
                <template v-for="medicationitem in medications">
                <div href="#" class="list-group-item" v-on:submit.prevent v-if="medicationitem.name">
                    <h4 class="list-group-item-heading">
                        <i class="glyphicon glyphicon-bullhorn" v-if="medicationitem.name"></i>
                        @{{ medicationitem.name }}
                        <textarea v-model="medicationitem.name" id="medication-edit-@{{ $index }}" style="display:none;">@{{ medicationitem.name }}</textarea>
                    </h4>

                    <h5>
                        <i class="glyphicon glyphicon-calendar" v-if="medicationitem.date"></i>
                        @{{ medicationitem.date }}
                    </h5>

                    <p class="list-group-item-text" v-if="medicationitem.name">@{{ medicationitem.description }}</p>
                    <button class="btn btn-xs btn-primary" v-if="medicationitem.name" v-on:click.stop.prevent="deleteEvent($index, $medication)" >Delete</button>
                    <button class="btn btn-xs btn-danger" v-if="medicationitem.name" v-on:click.stop.prevent="editEvent($index, $medication)">Edit</button>
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
    <script src="/js/careplan.js"></script>