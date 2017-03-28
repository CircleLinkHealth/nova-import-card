@extends('partials.adminUI')

@section('content')
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
                            <div class="panel-heading">Billable Patients Report</div>

                            <div class="panel-body">
                                <table class="table table-striped" id="enrollment_list">
                                    <thead>
                                    <tr>
                                        <th>
                                            Provider Name
                                        </th>
                                        <th>
                                            Patient Name
                                        </th>
                                        <th>
                                            DOB
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
                                            Approve
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
                $('#enrollment_list').DataTable({
                    processing: true,
                    serverSide: false,
                    "scrollX": true,
                    ajax: {
                        "url": '{!! url('/admin/reports/monthly-billing/v2/data') !!}',
                        "type": "POST",
                    },

                    columns: [
                        {data: 'provider', name: 'provider'},
                        {data: 'name', name: 'name'},
                        {data: 'dob', name: 'dob'},
                        {data: 'ccm', name: 'ccm'},
                        {data: 'problem1', name: 'problem1'},
                        {data: 'problem2', name: 'problem2'},
                        {data: 'approve', name: 'approve'},
                    ],
                    "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                        console.log(aData);
                        if (aData['background_color'] != '') {
                            $('td', nRow).css('background-color', aData['background_color']);
                        }
                    },
                    "iDisplayLength": 25,
                });

            });


        </script>
        <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>


@stop