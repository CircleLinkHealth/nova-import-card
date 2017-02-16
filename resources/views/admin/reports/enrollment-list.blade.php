@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Enrollment Report</div>

                            <div class="panel-body">
                                <table class="table table-striped" id="nurse_daily">
                                    <thead>
                                    <tr>
                                        <th>
                                            Patient Name
                                        </th>
                                        <th>
                                            Program
                                        </th>
                                        <th>
                                            Provider
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            MRN Number
                                        </th>
                                        <th>
                                            DOB
                                        </th>
                                        <th>
                                            Phone Number
                                        </th>
                                        <th>
                                            Attempt Count
                                        </th>
                                        <th>
                                            Invite Sent At
                                        </th>
                                        <th>
                                            Invite Opened At
                                        </th>
                                        <th>
                                            Last Attempt At
                                        </th>
                                        <th>
                                            Consented At
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

            $(function() {
                $('#nurse_daily').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: '{!! url('/admin/enroll/list/data') !!}',
                    columns: [
                        {data: 'name', name: 'name'},
                        {data: 'program', name: 'program'},
                        {data: 'provider', name: 'provider'},
                        {data: 'status', name: 'status'},
                        {data: 'mrn_number', name: 'mrn_number'},
                        {data: 'dob', name: 'dob'},
                        {data: 'phone', name: 'phone'},
                        {data: 'attempt_count', name: 'attempt_count'},
                        {data: 'invite_sent_at', name: 'invite_sent_at'},
                        {data: 'invite_opened_at', name: 'invite_opened_at'},
                        {data: 'last_attempt_at', name: 'last_attempt_at'},
                        {data: 'consented_at', name: 'consented_at'},
                    ],
//                    "aaSorting":[3,'desc'],
                    "iDisplayLength": 25
                });

            });


        </script>
        <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>


@stop