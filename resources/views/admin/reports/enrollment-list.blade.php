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
                                            DOB
                                        </th>
                                        <th>
                                            Consent Timestamp
                                        </th>
                                        <th>
                                            Phone Number
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
                        {data: 'dob', name: 'dob'},
                        {data: 'date', name: 'date'},
                        {data: 'phone', name: 'phone'},
                    ],
                    "iDisplayLength": 25
                });

            });

        </script>
        <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>


@stop