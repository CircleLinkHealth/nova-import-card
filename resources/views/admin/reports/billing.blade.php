@extends('partials.adminUI')

@section('content')
    <style>
        .select2-container {
            width: 300px !important;
        }
    </style>

    <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">

    <!-- Modal -->
    <div class="modal fade" id="problemPicker" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <button type="button" class="close"
                            data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <h4 class="modal-title" id="problem-modal-title">
                        Select Eligible Problem for <span id="patientName"></span>
                    </h4>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form name="problem_form" id="problem_form">
                        <div class="form-group">
                            <label for="select_problem">Eligible Problems</label>
                            <select class="form-control"
                                    id="select_problem" name="select_problem">

                            </select>
                        </div>

                        <div id="showOther" class="form-group" style="display:none">
                            <label for="otherProblem">If other, please specify</label>
                            <input class="form-control" name="otherProblem" id="otherProblem">
                        </div>

                        <div class="form-group">
                            <label for="code">Problem ICD10 Code</label>
                            <input class="form-control" name="code" id="code">
                        </div>

                        <input type="hidden" id="report_id" name="report_id">
                        <input type="hidden" id="problem_no" name="problem_no">
                        <input type="hidden" id="has_problem" name="has_problem">
                        <input type="hidden" id="modal_date" name="modal_date">
                        <input type="hidden" id="modal_practice_id" name="modal_practice_id">
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal">
                        Close
                    </button>
                    <button type="button" id="confirm_problem" class="btn btn-primary">
                        Save changes
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>
        <div class="row" style="margin-left: -52px; margin-right: -55px;">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Approve Billable Patients
                            </div>
                            <div class="panel-body">

                                <div class="col-md-12 row">
                                    <div class="col-md-5">

                                        <label class="col-md-2 control-label" for="practice_id">Select Practice</label>
                                        <select class="col-md-3 practices dropdown Valid form-control reloader"
                                                name="practice_id" id="practice_id">
                                            @foreach($practices as $practice)
                                                <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                    <div class="col-md-5">

                                        <label class="col-md-2 control-label" for="date">Select Month</label>
                                        <select class="col-md-3 practices dropdown Valid form-control reloader"
                                                name="date" id="date">
                                            @foreach($dates as $key => $val)

                                                @if(\Carbon\Carbon::today()->firstOfMonth()->toDateString() == $key)
                                                    <option value="{{$key}}" selected>{{$val}}</option>
                                                @else
                                                    <option value="{{$key}}">{{$val}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="col-md-12 row">
                                    <h5 style="line-height: 20px;">
                                        <div class="col-md-3"><span><b>Practice Approved: </b></span><span
                                                    id="approved-count"
                                                    style="color: green">{{$approved}}</span><br>
                                        </div>
                                        <div class="col-md-3"><span><b>Practice Flagged: </b></span><span
                                                    style="color: darkorange"
                                                    id="toQA-count">{{$toQA}}</span><br>
                                        </div>
                                        <div class="col-md-3"><span><b>Practice Rejected: </b></span><span
                                                    style="color: darkred"
                                                    id="rejected-count">{{$rejected}}</span><br>
                                        </div>
                                    </h5>
                                </div>
                            </div>


                            <hr>
                            <div class="panel-body">
                                <table class="table table-striped" id="billable_list">
                                    <thead>
                                    <tr>
                                        <th>
                                            Provider Name
                                        </th>
                                        <th>
                                            Patient Name
                                        </th>
                                        <th>
                                            Practice Name
                                        </th>
                                        <th>
                                            DOB
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            CCM (Mins)
                                        </th>
                                        <th>
                                            Problem 1
                                        </th>
                                        <th>
                                            Problem 1 Code
                                        </th>
                                        <th>
                                            Problem 2
                                        </th>
                                        <th>
                                            Problem 2 Code
                                        </th>
                                        <th>
                                            #Successful Calls
                                        </th>
                                        <th>
                                            Approve
                                        </th>
                                        <th>
                                            Reject
                                        </th>
                                        <th>
                                            Report
                                        </th>
                                        <th>
                                            QA
                                        </th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>

            $(function () {

                $('#billable_list').DataTable({

                    processing: true,
                    serverSide: false,
                    "scrollX": true,
                    select: true,
                    ajax: {
                        "url": '{!! url('/admin/reports/monthly-billing/v2/data') !!}',
                        "type": "POST",
                        "data": function (d) {
                            d.practice_id = $('#practice_id').val();
                            d.date        = $("#date option:selected").text()
                        }
                    },
                    columns: [
                        {data: 'provider', name: 'provider'},
                        {data: 'name', name: 'name'},
                        {data: 'practice', name: 'practice'},
                        {data: 'dob', name: 'dob'},
                        {data: 'status', name: 'status'},
                        {data: 'ccm', name: 'ccm'},
                        {data: 'problem1', name: 'problem1'},
                        {data: 'problem1_code', name: 'problem1_code'},
                        {data: 'problem2', name: 'problem2'},
                        {data: 'problem2_code', name: 'problem2_code'},
                        {data: 'no_of_successful_calls', name: 'no_of_successful_calls'},
                        {data: 'approve', name: 'approve'},
                        {data: 'reject', name: 'reject'},
                        {data: 'report_id', name: 'report_id'},
                        {data: 'qa', name: 'qa'},
                    ],
                    dom: 'Bfrtip',
                    buttons: [
                        'excel'
                    ],
                    "columnDefs": [
                        {
                            "targets": [13, 14],
                            "visible": false,
                            "searchable": false
                        }
                    ],
                    "iDisplayLength": 25,
                    "aaSorting": [14, 'desc'],
                    "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                        if (aData['background_color'] != '') {
                            $('td', nRow).css('background-color', aData['background_color']);
                        }
                    },
                    "initComplete": function (settings, json) {

                    },
                });

                //When a new practice is picked, update table
                $('.reloader').on('change', function () {

                    $('#billable_list').DataTable().ajax.reload();

                    setLoadingLabels();

                    var url = '{!! route('monthly.billing.count') !!}';

                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {
                            practice_id: $('#practice_id').val(),
                            date: $("#date option:selected").text()
                        },

                        success: function (data) {

                            updateBillingCounts(data);

                        }
                    });


                });

                $('#select_problem').on('change', function () {

                    if ($("#select_problem option:selected").text() == 'Other') {

                        $("#showOther").css('display', 'block');

                    } else {

                        $("#showOther").css('display', 'none');

                    }

                });


                $(".practices").select2();

                //HANDLE ACCEPTANCE
                $('#billable_list').on('change', '.approved_checkbox', function () {

                    setLoadingLabels();

                    var rejectedBox = $('#' + this.id + '.rejected_checkbox');
                    let approved    = 0;

                    if ($(this).is(':checked')) {
//
                        approved = 1;

                        if (rejectedBox.is(':checked') == true) {

                            rejectedBox.attr('checked', false);

                        }
//
                    }

                    var url = '{!! route('monthly.billing.approve') !!}';

                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {
                            //send report id to mark
                            report_id: this.id,
                            practice_id: $('#practice_id').val(),
                            approved: approved,
                            date: $("#date option:selected").text()
                        },

                        success: function (data) {

                            console.log(data);
                            updateBillingCounts(data.counts);

                        }
                    });

                });

                //HANDLE REJECTION
                $('#billable_list').on('change', '.rejected_checkbox', function () {

                    setLoadingLabels();

                    var approveBox = $('.approved_checkbox#' + this.id);
                    var rejected   = 0;

                    if ($(this).is(':checked')) {

                        rejected = 1;

                        if (approveBox.is(':checked') == true) {

                            approveBox.attr('checked', false);

                        }

                    }

                    var url = '{!! route('monthly.billing.reject') !!}';


                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {
                            //send report id to mark
                            report_id: this.id,
                            practice_id: $('#practice_id').val(),
                            rejected: rejected,
                            date: $("#date option:selected").text()

                        },

                        success: function (data) {

                            updateBillingCounts(data.counts);

                        }
                    });

                });

                //BUILD MODAL FOR PROBLEM PICKER
                $('#billable_list').on('click', '.problemPicker', function () {

                    name = $(this).attr('patient');
                    console.log(name);
                    $("#patientName").html(name);

                    $('#report_id').val(this.id);
                    $('#problem_no').val(this.name);
                    $('#has_problem').val(0);

                    //to update the billing counts
                    let practice = $('#practice_id option:selected').val();
                    $('#modal_practice_id').val(practice);
                    let date = $("#date option:selected").text();
                    $('#modal_date').val(date);

                    $('#otherProblem').empty();
                    $('#select_problem').empty();

                    //the options are stored in a | delimted string
                    $.each(this.value.split('|'), function (key, value) {
                        $('#select_problem').append('<option value="' + value + '">' + (value == '-1' ? 'select' : value) + '</option>');
                    });

                    $('#select_problem').append('<option value="other" name="other">Other</option>');

                    $('#problemPicker').modal('show');

                });

                //BUILD MODAL FOR CODE PICKER
                $('#billable_list').on('click', '.codePicker', function () {

                    name = $(this).attr('patient');
                    console.log(name);
                    $("#patientName").html(name);

                    $('#report_id').val(this.id);
                    $('#problem_no').val(this.name);
                    $('#has_problem').val(1);


                    let practice = $("#practice_id option:selected").text();
                    $('#modal_practice_id').val(practice);

                    let date = $("#date option:selected").text();
                    $('#modal_date').val(date);

                    console.log(practice);
                    console.log(date);

                    $('#otherProblem').empty();
                    $('#select_problem').empty();

                    //the options are stored in a | delimted string
                    $('#select_problem').append('<option value="' + this.value + '" selected disabled>' + this.value + '</option>');

                    $('#problemPicker').modal('show');

                });

                //HANDLE MODAL SUBMIT
                $('#confirm_problem').click(function () {

                    setLoadingLabels();

                    let url = '{!!route('monthly.billing.store-problem')!!}';

                    $("#showOther").css('display', 'none');

                    $.ajax({
                        type: "POST",
                        url: url,
                        data: $('#problem_form').serialize(),

                        success: function (data) {

                            updateBillingCounts(data.counts);
                            console.log(data.counts);

                            $('#billable_list').DataTable().ajax.reload();
                            $('#problemPicker').modal('hide');

                            //set the modal to cleared for further use
                            $('#select_problem').val('');
                            $('#otherProblem').val('');
                            $('#code').val('');
                            $('#report_id').val('');
                            $('#problem_no').val('');

                        }
                    });


                })

            });

            function updateBillingCounts(data) {

                $("#approved-count").text(data.approved);
                $("#toQA-count").text(data.toQA);
                $("#rejected-count").text(data.rejected);

            }

            function setLoadingLabels() {

                $("#approved-count").text('Loading...');
                $("#toQA-count").text('Loading...');
                $("#rejected-count").text('Loading...');

            }


        </script>

        <script src="//cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
        <script src="//cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/pdfmake.min.js"></script>
        <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/vfs_fonts.js"></script>
        <script src="//cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>
        <script src="//cdn.datatables.net/buttons/1.3.1/js/buttons.print.min.js"></script>



    </div>
@stop