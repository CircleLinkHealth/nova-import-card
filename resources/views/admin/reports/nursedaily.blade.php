@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
    @endpush

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
                                <nurse-daily-report></nurse-daily-report>
                            </div>
{{--                            <div class="panel-body">--}}
{{--                                <table class="table table-striped" id="nurse_daily">--}}
{{--                                    <thead>--}}
{{--                                    <tr>--}}
{{--                                        <th>--}}
{{--                                            Nurse Name--}}
{{--                                        </th>--}}
{{--                                        <th>--}}
{{--                                            Time Since Last Activity--}}
{{--                                        </th>--}}
{{--                                        <th>--}}
{{--                                            # Scheduled Calls Today--}}
{{--                                        </th>--}}
{{--                                        <th>--}}
{{--                                            # Completed Calls Today--}}
{{--                                        </th>--}}
{{--                                        <th>--}}
{{--                                            # Successful Calls Today--}}
{{--                                        </th>--}}
{{--                                        <th>--}}
{{--                                            CCM Mins Today--}}
{{--                                        </th>--}}
{{--                                        --}}{{--<th>--}}
{{--                                            --}}{{--Total Mins Today--}}
{{--                                        --}}{{--</th>--}}
{{--                                        <th id="last" class="last">--}}
{{--                                            Last Activity--}}
{{--                                        </th>--}}
{{--                                    </tr>--}}
{{--                                    </thead>--}}
{{--                                </table>--}}
{{--                            </div>--}}
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>
        {{--<script type="text/javascript" src="dataTables.numericComma.js"></script>--}}

    
    @push('scripts')
        <script>

            $(function() {
                $('#nurse_daily').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: '{!! url('/admin/reports/nurse/daily/data') !!}',
                    columns: [
                        {data: 'name', name: 'name'},
                        {data: 'Time Since Last Activity', name: 'Time Since Last Activity'},
                        {data: '# Scheduled Calls Today', name: '# Scheduled Calls Today'},
                        {data: '# Completed Calls Today', name: '# Completed Calls Today'},
                        {data: '# Successful Calls Today', name: '# Successful Calls Today'},
                        {data: 'CCM Mins Today', name: 'CCM Mins Today'},
                        {data: 'last_activity', name: 'Last Activity'},
                    ],
                    "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                        console.log(aData['lessThan20MinsAgo']);
                        if ( aData['lessThan20MinsAgo'] == true )
                        {
                            $('td', nRow).css('background-color', 'rgba(151, 218, 172, 1)');
                        }
                    },
                    "aaSorting":[6,'desc'],
                    "iDisplayLength": 50,
                    "columnDefs": [
                        { "type": "date", targets: 'last_activity' }
                    ]
                });

            });

        </script>
        <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
    @endpush


@stop