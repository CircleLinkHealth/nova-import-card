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
                            <div class="panel-heading">Nurse Daily Report</div>

                            <div class="panel-body">
                                <table class="table table-striped" id="nurse_daily">
                                    <thead>
                                    <tr>
                                        <th>
                                            Nurse Name
                                        </th>
                                        <th>
                                            Time Since Last Activity
                                        </th>
                                        <th>
                                            # Calls Made Today
                                        </th>
                                        <th>
                                            # Successful Calls Made Today
                                        </th>
                                        <th>
                                            CCM Time Accrued Today (mins)
                                        </th>
                                        <th>
                                            Last Activity
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
                    ajax: '{!! url('/admin/reports/nurse/daily/data') !!}',
                    columns: [
                        {data: 'name', name: 'name'},
                        {data: 'Time Since Last Activity', name: 'Time Since Last Activity'},
                        {data: '# Successful Calls Made Today', name: '# Successful Calls Made Today'},
                        {data: '# Calls Made Today', name: '# Calls Made Today'},
                        {data: 'CCM Time Accrued Today (mins)', name: 'CCM Time Accrued Today (mins)'},
                        {data: 'last_activity', name: 'Last Activity'},
                    ],
                    "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                        console.log(aData['lessThan20MinsAgo']);
                        if ( aData['lessThan20MinsAgo'] == true )
                        {
                            $('td', nRow).css('background-color', 'rgba(151, 218, 172, 1)');
                        }
                    },
                    "iDisplayLength": 25,
                    "columnDefs": [
                        { "type": "date", targets: 'last_activity' }
                    ]
                });
            });



        </script>
        <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>


@stop