@extends('partials.adminUI')

@section('content')
    <style>
        .select2-container {
            width: 300px !important;
        }
    </style>

    <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">

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

                                <div class="row" style="padding-bottom: 20px;">

                                </div>

                                <div class="col-md-12 row">

                                    <div class="col-md-8" style="padding-bottom: 27px;">

                                        <label class="col-md-1 control-label" for="practice_id">Select Practice</label>
                                        <select class="col-md-4 practices dropdown Valid form-control"
                                                name="practice_id" id="practice_id">

                                            <option value="0">All</option>
                                            @foreach($practices as $practice)
                                                <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                                            @endforeach

                                        </select>

                                    </div>

                                    <div class="col-md-4">
                                        <h5>
                                            <span><b>Approved: </b></span><span id="approved-count" style="color: green">{{$approved}}</span><br>
                                            <span><b>to QA: </b></span><span style="color: darkorange" id="toQA-count">{{$toQA}}</span><br>
                                            <span><b>Rejected: </b></span><span style="color: darkred" id="rejected-count">{{$rejected}}</span><br>
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
                                                Problem 2
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
                            {data: 'name', name: 'name'},
                            {data: 'provider', name: 'provider'},
                            {data: 'practice', name: 'practice'},
                            {data: 'dob', name: 'dob'},
                            {data: 'status', name: 'status'},
                            {data: 'ccm', name: 'ccm'},
                            {data: 'problem1', name: 'problem1'},
                            {data: 'problem2', name: 'problem2'},
                            {data: 'no_of_successful_calls', name: 'no_of_successful_calls'},
                            {data: 'approve', name: 'approve'},
                            {data: 'reject', name: 'reject'},
                            {data: 'report_id', name: 'report_id'},
                        ],

                        "columnDefs": [
                            {
                                "targets": [11],
                                "visible": false,
                                "searchable": false
                            }
                        ],
                        "iDisplayLength": 25,
                        "aaSorting": [1, 'desc'],
                        "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                            if (aData['background_color'] != '') {
                                $('td', nRow).css('background-color', aData['background_color']);
                            }
                        },

                    });

                    $('#practice_id').on('change', function () {

                        $('#billable_list').DataTable().ajax.reload();

                    });


                    $(".practices").select2();


                    //HANDLE ACCEPTANCE
                    $('#billable_list').on('change', '.approved_checkbox', function () {
//
                        var url = '{!! route('monthly.billing.approve') !!}';
                        var current = $('#approved-count').html();
                        var rejectBox = $('.rejected_checkbox#' + this.id);

                        //if none were checked, gotta -- QA
                        if(rejectBox.is(':checked') == false && $(this).is(':checked') == true){

                            $("#toQA-count").text(parseInt($("#toQA-count").html()) - 1);

                        }

                        if ($(this).is(':checked')) {

                            var approved = 1;

                            current = $('#approved-count').html();
                            $("#approved-count").text(parseInt(current) + 1);

                            rejectBox.attr('checked', false);

                        } else {

                            var approved = 0;
                            $("#approved-count").text(parseInt(current) - 1);

                            //if both are unchecked, gotta ++ QA count
                            if(rejectBox.is(':checked') == false){

                                $("#toQA-count").text(parseInt($("#toQA-count").html()) + 1);

                            }

                        }



                        console.log($(this).is(':checked'));

                        $.ajax({
                            type: "POST",
                            url: url,
                            data: {
                                //send report id to mark
                                report_id: this.id,
                                approved: approved
                            },

                            success: function (data) {

                                console.log(data);

                            }
                        });

                    });

                    //HANDLE REJECTION
                    $('#billable_list').on('change', '.rejected_checkbox', function () {

                        var url = '{!! route('monthly.billing.reject') !!}';
                        var current = $('#rejected-count').html();
                        var approveBox = $('.rejected_checkbox#' + this.id);

                        //if none were checked, gotta -- QA
                        if(approveBox.is(':checked') == false && $(this).is(':checked') == true){

                            $("#toQA-count").text(parseInt($("#toQA-count").html()) - 1);

                        }

                        if ($(this).is(':checked')) {

                            var rejected = 1;
                            $("#rejected-count").text(parseInt(current) + 1);
                            $('.approved_checkbox#' + this.id).attr('checked', false);

                        } else {

                            var rejected = 0;
                            $("#rejected-count").text(parseInt(current) - 1);

                            //if both are unchecked, gotta ++ QA count
                            if($('.approved_checkbox#' + this.id).is(':checked') == false){

                                $("#toQA-count").text(parseInt($("#toQA-count").html()) + 1);

                            }

                        }


                        console.log($(this).is(':checked'));

                        $.ajax({
                            type: "POST",
                            url: url,
                            data: {
                                //send report id to mark
                                report_id: this.id,
                                rejected: rejected,
                            },

                            success: function (data) {

                                console.log(data);

                            }
                        });

                    });

                });


            </script>

            <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>

@stop