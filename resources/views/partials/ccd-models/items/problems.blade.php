    <!-- main body of our application -->
    <div class="row" id="problems">

        <!-- show the problems -->
        <div class="col-sm-12">
            <div class="list-group">
                <template v-for="problemitem in problems">
                <div href="#" class="list-group-item" v-on:submit.prevent v-if="problemitem.name">
                    <h4 class="list-group-item-heading" v-if="problemitem.name">
                        <span id="problem-name-@{{ $index }}"><i class="glyphicon glyphicon-asterisk"></i> @{{ problemitem.name }}</span>
                        <textarea v-model="problemitem.name" id="problem-edit-@{{ $index }}" style="display:none;">@{{ problemitem.name }}</textarea>
                        <input type="hidden" name="id" value="@{{ problemitem.id }}">
                        <input type="hidden" name="patient_id" value="@{{ problemitem.patient_id }}">
                    </h4>

                    <p class="list-group-item-text" v-if="problemitem.name">@{{ problemitem.description }}</p>
                    <button class="btn btn-xs btn-danger problem-delete-btn" v-if="problemitem.name" v-on:click.stop.prevent="deleteProblem($index, $problem)" >Delete</button>
                    <button class="btn btn-xs btn-primary problem-edit-btn" v-if="problemitem.name" v-on:click.stop.prevent="editProblem($index, $problem)">Edit</button>
                    <button class="btn btn-xs btn-success problem-save-btn" id="problem-save-btn-@{{ $index }}" v-if="problemitem.name" v-on:click.stop.prevent="updateProblem($index, $problem)" style="display:none;">Save</button>
                </div>
                </template>

            </div>
        </div>


        <!-- add an problem form -->
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3>Add a Problem</h3>
                </div>
                <div class="panel-body">

                    <input type="hidden" id="patient_id" name="patient_id" value="{{ $patient->ID }}">
                    <div class="form-group">
                        <input class="form-control" placeholder="Problem Name" v-model="problem.name">
                    </div>

                    <button class="btn btn-primary" v-on:click.stop.prevent="addProblem()">Submit</button>

                </div>
            </div>
        </div>

    </div>



    <!-- JS -->
    <script src="/js/problemsItem.js"></script>