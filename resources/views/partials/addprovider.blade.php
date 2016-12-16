<script>
    $(document).ready(function () {
        $("#addNewProvider").click(function (e) {
            $("#addProvider").modal();
            e.preventDefault();
            return false;
        });
    });

</script>

<style>
    .providerForm {
        padding: 10px;
    }
</style>

<div id="addProvider" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Please Add Provider Details</h4>
            </div>
            <div class="modal-body">
                <form id="form" name="create" method="post"
                      action="{{URL::route('provider.store', array('patientId' => $patient->id))}}">
                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="name">Provider Name</label>
                            <div class="col-md-3">
                                <input id="first_name" name="first_name" type="text" placeholder="First"
                                       class="form-control input-md"
                                       required="required">
                            </div>
                            <div class="col-md-3">
                                <input id="last_name" name="last_name" type="text" placeholder="Last"
                                       class="form-control input-md"
                                       required="required">
                            </div>
                        </div>
                    </div>

                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="speciality">Specialty or Service Type</label>
                            <div class="col-md-6">
                                <input id="speciality" name="speciality" type="text" placeholder=""
                                       class="form-control input-md"
                                       required="required">
                            </div>
                        </div>
                    </div>

                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="address">Address</label>
                            <div class="col-md-6">
                                <input id="address" name="address" type="text" placeholder=""
                                       class="form-control input-md"
                                       required="">

                            </div>
                        </div>
                    </div>

                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="phone">Phone Number</label>
                            <div class="col-md-6">
                                <input id="phone" name="phone" type="text" placeholder=""
                                       class="form-control input-md"
                                       required="">

                            </div>
                        </div>
                    </div>

                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="practice">Practice Name</label>
                            <div class="col-md-6">
                                <input id="practice" name="practice" type="text" placeholder=""
                                       class="form-control input-md"
                                       required="">

                            </div>
                        </div>
                    </div>

                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="email">Email</label>
                            <div class="col-md-6">
                                <input id="email" name="email" type="email" placeholder=""
                                       class="form-control input-md"
                                       required="">

                            </div>
                        </div>
                    </div>


                    <div class="row providerForm">
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="type">Select Type</label>
                            <div class="col-md-6">
                                <select id="type" name="type" class="form-control type">
                                    <option value="clinical">Clinical (MD, RN or other)</option>
                                    <option value="non-clinical">Non-clinical</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="created_by" name="created_by" value="{{auth()->user()->id}}">
                    <input type="hidden" id="patient_id" name="patient_id" value="{{$patient->id}}">

                    <div class="modal-footer">
                        <div class="row result">

                        </div>
                    </div>


                    <div>
                        <button type="submit" id="create" class="create btn btn-primary">Add</button>
                    </div>

                </form>

            </div>

        </div>

    </div>
</div>


