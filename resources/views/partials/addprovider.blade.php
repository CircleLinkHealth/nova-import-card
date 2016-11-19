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
                            <div class="col-md-6">
                                <input id="name" name="name" type="text" placeholder=""
                                       class="form-control input-md"
                                       required="">
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
                                    <option value="clinical">Clinical (MD, RN or other clinician)</option>
                                    <option value="non-clinical">Non-clinical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <input type="hidden" name="created_by" value="{{auth()->user()->id}}">

            <div class="row">
                <div class="modal-footer">
                    <button type="submit" class="create" id="create" class="btn btn-primary">Add</button>
                </div>
            </div>
            <div class="row">
                <span class="result"></span>
            </div>
        </div>
        </form>

    </div>
</div>


<script>

    $("#create").on('click',function () {
        var url = '{!! route('provider.store') !!}'; // the script where you handle the form input.

        var frm = $('#contactForm1');

        $.ajax({
            type: "POST",
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                phone: $('#phone').val(),
                name: $('#name').val(),
                practice: $('#practice').val(),
                type: $('#type').val(),
                email: $('#email').val(),
            },
            success: function (data) {
                console.log(data); // show response from the php script.
                $('#result').text(data);
            }
        });
        return false;
    });

</script>