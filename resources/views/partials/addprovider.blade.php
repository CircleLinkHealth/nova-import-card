<style>
    .providerForm {
        padding: 10px;
    }
</style>

<div id="success" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Care Person Created!</h4>
            </div>
            <div class="modal-body">
                <p><span id="newProviderName"></span> will be added to the patient's care team.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="addProviderModal" class="modal fade" role="dialog">
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

                    {{ csrf_field() }}
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
                            <label class="col-md-3 control-label" for="specialty">Specialty or Service Type</label>
                            <div class="col-md-6">
                                <input id="specialty" name="specialty" type="text" placeholder=""
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
                        <button type="submit" id="createCarePerson" class="create btn btn-primary">Add</button>
                    </div>

                </form>

            </div>

        </div>

    </div>
</div>

<script>

    $("#createCarePerson").on('click', function () {

        var url = '{!! route('provider.store') !!}'; // the script where you handle the form input.
        var name = null;
        var id = null;

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
                provider: $('#provider').val(),
                specialty: $('#specialty').val(),
                practice: $('#practice').val(),
                type: $('#type').val(),
                email: $('#email').val(),
                is_completed: $('#is_completed').val(),
                created_by: $('#created_by').val(),
                patient_id: $('#patient_id').val()
            },
            success: function (data) {


                var dataArray = JSON.parse(data);

                //setting up the select2 and dynamic picking wasn't working,
                //quick work around to replace the whole innerhtml with a
                //disabled div

                $('#providerBox').replaceWith("" +
                    "<select id='provider' " +
                    "name='provider' " +
                    "class='provider selectpickerX dropdownValid form-control' " +
                    "data-size='10' disabled>  " +
                    "<option value=" + dataArray['user_id'] + ">" + dataArray['name'] +
                    "</option>");
                $('#providerDiv').css('padding-bottom', '10px');
                $("#save").append('<input type="hidden" value="' + dataArray['user_id'] + '" id="provider" name="provider">');
                $("#addProviderModal").modal('hide');
                console.log(dataArray);
                $("#newProviderName").text(dataArray['name']);
                $("#success").modal('show');
            }
        });

        return false;
    });


    $("#addNewProviderFAB").click(function (e) {
        $("#addProviderModal").modal();
        e.preventDefault();
        return false;
    });

    $("#addNewProvider").click(function (e) {
        $("#addProviderModal").modal();
        e.preventDefault();
        return false;
    });

</script>





