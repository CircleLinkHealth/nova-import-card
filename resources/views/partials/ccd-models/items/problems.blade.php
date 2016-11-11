    <!-- main body of our application -->
    <div class="row" id="problems">

        <!-- show the problems -->
        <div class="col-sm-12">
            <div class="list-group">
                <template v-for="problemitem in problems">
                    <div class="list-group-item" v-on:submit.prevent v-if="problemitem.name"
                         style="padding:5px;font-size:12px;">
                    <div class="row">
                        <div class="col-sm-10">
                            <div class="list-group-item-heading" v-if="problemitem.name">
                                <span id="problem-name-@{{ $index }}">@{{ problemitem.name }}</span>
                                <textarea v-model="problemitem.name" id="problem-edit-@{{ $index }}" style="display:none;" rows="10">@{{ problemitem.name }}</textarea>
                                <input type="hidden" name="id" value="@{{ problemitem.id }}">
                                <input type="hidden" name="patient_id" value="@{{ problemitem.patient_id }}">
                            </div>
                        </div>
                    <div class="col-sm-2 text-right">

                        <p class="list-group-item-text" v-if="problemitem.name">@{{ problemitem.description }}</p>
                        <button class="btn btn-xs btn-danger problem-delete-btn" v-if="problemitem.name" v-on:click.stop.prevent="deleteProblem($index, $problem)" ><span><i class="glyphicon glyphicon-remove"></i></span></button>
                        <button class="btn btn-xs btn-primary problem-edit-btn" v-if="problemitem.name" v-on:click.stop.prevent="editProblem($index, $problem)"><span><i class="glyphicon glyphicon-pencil"></i></span></button>
                        <button class="btn btn-xs btn-success problem-save-btn" id="problem-save-btn-@{{ $index }}" v-if="problemitem.name" v-on:click.stop.prevent="updateProblem($index, $problem)" style="display:none;"><span><i class="glyphicon glyphicon-ok"></i></span></button>
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
                            <input type="hidden" id="patient_id" name="patient_id" value="{{ $patient->id }}">
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



    <!-- JS -->
    <script src="/js/problemsItem.js"></script>