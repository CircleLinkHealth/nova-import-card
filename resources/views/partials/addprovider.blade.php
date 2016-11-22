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
                                       required="first_name">
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
                            <label class="col-md-3 control-label" for="speciality">Speciality</label>
                            <div class="col-md-6">
                                <input id="speciality" name="speciality" type="text" placeholder=""
                                       class="form-control input-md"
                                       required="">
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
                            <label class="col-md-3 control-label" for="practice">Practice/Program Name</label>
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
                                <select id="type" name="type" class="form-control">
                                    <option value="clinical">Clinical (MD)</option>
                                    <option value="non-clinical">Non-clinical</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="created_by" name="created_by" value="{{auth()->user()->id}}">
                    <input type="hidden" id="patient_id" name="patient_id" value="{{$patient->id}}">

                    <div>
                        <button type="submit" id="create" class="create btn btn-primary">Add</button>
                    </div>

                    <div class="modal-footer">
                    </div>

                </form>

                <div class="row">

                </div>
                <div class="row">
                    <span class="result"></span>
                </div>
            </div>

        </div>

    </div>
</div>


<script>

    $("#create").on('click', function () {
        var url = '{!! route('provider.store') !!}'; // the script where you handle the form input.

        $.ajax({
            type: "POST",
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                phone: $('#phone').val(),
                first_name: $('#first_name').val(),
                address: $('#address').val(),
                last_name: $('#last_name').val(),
                specialty: $('#specialty').val(),
                practice: $('#practice').val(),
                type: $('#type').val(),
                email: $('#email').val(),
                created_by: $('#created_by').val(),
                patient_id: $('#patient_id').val(),
            },
            success: function (data) {
                console.log(data); // show response from the php script.
                $('#result').text("data");
            }
        });
        return false;
    });

</script>