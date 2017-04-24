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
                        Select Eligible Problem
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

                        <div class="form-group">
                            <label for="otherProblem">If other, please specify</label>
                            <input class="form-control" name="otherProblem" id="otherProblem">
                        </div>

                        <div class="form-group">
                            <label for="code">Problem ICD10 Code</label>
                            <input class="form-control" name="code" id="code">
                        </div>

                        <input type="hidden" id="report_id" name="report_id">
                        <input type="hidden" id="problem_no" name="problem_no">
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
                            <div class="panel-heading">Approve Billable Patients (Currently supports {{$currentMonth}})
                            </div>
                            <div class="panel-body">

                                <div class="col-md-12 row">
                                    <div class="col-md-5">

                                        <label class="col-md-2 control-label" for="practice_id">Select Practice</label>
                                        <select class="col-md-3 practices dropdown Valid form-control"
                                                name="practice_id" id="practice_id">
                                            @foreach($practices as $practice)
                                                <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                    <div class="col-md-5">

                                        <label class="col-md-2 control-label" for="date">Select Month</label>
                                        <select class="col-md-3 practices dropdown Valid form-control"
                                                name="date" id="date">
                                            <option value="2017-03-01" selected disabled>March 2017</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="col-md-12 row">
                                    <h5 style="line-height: 20px;">
                                        <div class="col-md-3"><span><b>Total Approved: </b></span><span id="approved-count"
                                                                                                  style="color: green">{{$approved}}</span><br>
                                        </div>
                                        <div class="col-md-3"><span><b>Total Flagged: </b></span><span
                                                    style="color: darkorange"
                                                    id="toQA-count">{{$toQA}}</span><br>
                                        </div>
                                        <div class="col-md-3"><span><b>Total Rejected: </b></span><span
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
                        console.log(json)
                    }


                });

                //When a new practice is picked, update table
                $('#practice_id').on('change', function () {

                    $('#billable_list').DataTable().ajax.reload();

                    console.log($('#practice_id').val());

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
                            approved: approved,
                            date: $("#date option:selected").text()
                        },

                        success: function (data) {

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

                    $('#report_id').val(this.id);
                    $('#problem_no').val(this.name);

                    $('#otherProblem').empty();
                    $('#select_problem').empty();

                    //the options are stored in a | delimted string
                    $.each(this.value.split('|'), function (key, value) {
                        $('#select_problem').append('<option value="' + value + '">' + (value == '-1' ? 'select' : value) + '</option>');
                    });

                    $('#select_problem').append('<option value="other" name="other">Other</option>');

                    $('#problemPicker').modal('show');

                });

                //HANDLE MODAL SUBMIT
                $('#confirm_problem').click(function () {

                    let url = '{!!route('monthly.billing.store-problem')!!}'

                    $.ajax({
                        type: "POST",
                        url: url,
                        data: $('#problem_form').serialize(),

                        success: function (data) {

                            $('#billable_list').DataTable().ajax.reload();
                            $('#problemPicker').modal('hide');

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

        <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
    </div>
@stop