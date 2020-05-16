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
        <div class="row" style="margin-left: -52px; margin-right: -55px;">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Ambassador KPIs</div>
                            <div class="panel-body">

                                <div class="col-md-12">
                                    <div class="row" style="padding-bottom: 27px;">

                                        <label class="col-md-1 control-label" for="textinput">Start Date</label>
                                        <input class="col-md-2" id="start_date" name="start_date"
                                               value="{{Carbon\Carbon::now()->startOfMonth()->toDateString()}}"
                                               type="date"
                                               placeholder="placeholder">
                                        <label class="col-md-1 control-label" for="textinput">End Date</label>
                                        <input class="col-md-2" id="end_date" name="end_date"
                                               value="{{Carbon\Carbon::now()->toDateString()}}" type="date"
                                               placeholder="placeholder">
                                    </div>
                                </div>

                                <hr>

                                <table class="table table-striped" id="ambassador_kpis">
                                    <thead>
                                    <tr>
                                        <th>
                                            Ambassador Name
                                        </th>
                                        <th>
                                            Total Hours
                                        </th>
                                        <th>
                                            #Enrolled
                                        </th>
                                        <th>
                                            #Called
                                        </th>
                                        <th>
                                            Calls/Hour
                                        </th>
                                        <th>
                                            Mins/Enrollment
                                        </th>
                                        <th>
                                            Conversion
                                        </th>
                                        <th>
                                            Hourly Rate
                                        </th>
                                        <th>
                                            Cost per Enrollment
                                        </th>
                                        <th>
                                            Earnings
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

        @push('scripts')
            <script>
                var buttonCommon = {
                    exportOptions: {
                        format: {
                            body: function ( data, row, column, node ) {
                                //just in case we want to format data
                                return data;
                            }
                        }
                    }
                };

                $(function () {

                    var columnsForFormating = [1, 2, 3, 4, 5, 6, 7, 8, 9];
                    $("#start_date").change(function () {
                        // whatever you need to be done on change of the input field
                    });

                    $("#end_date").change(function () {
                        // whatever you need to be done on change of the input field
                    });

                    $('#ambassador_kpis').DataTable({
                        processing: true,
                        serverSide: false,
                        "scrollX": true,
                        "columnDefs": [
                            {
                                className: 'dt-center',
                                targets: columnsForFormating
                            },
                        ],
                        ajax: {
                            "url": '{!! url('/admin/enrollment/ambassador/kpis/data') !!}',
                            "type": "GET",
                            "data": function (d) {
                                d.start_date = $('#start_date').val();
                                d.end_date = $('#end_date').val();
                            }
                        },
                        dom: 'Bfrtip',
                        buttons: [
                            $.extend( true, {}, buttonCommon, {
                                extend: 'copyHtml5',
                            } ),
                            $.extend( true, {}, buttonCommon, {
                                extend: 'excelHtml5',
                                title: 'Care Ambassador KPIs CSV Export'
                            } ),
                            $.extend( true, {}, buttonCommon, {
                                extend: 'pdfHtml5',
                                orientation: 'portrait',
                                pageSize: 'A4',
                                title: 'Care Ambassador KPIs PDF Export'
                            } )
                        ],
                        columns: [

                            {data: 'name', name: 'name'},
                            {data: 'total_hours', name: 'total_hours'},
                            {data: 'no_enrolled', name: 'no_enrolled'},
                            {data: 'total_calls', name: 'total_calls'},
                            {data: 'calls_per_hour', name: 'calls_per_hour'},
                            {data: 'mins_per_enrollment', name: 'mins_per_enrollment'},
                            {data: 'conversion', name: 'conversion'},
                            {data: 'hourly_rate', name: 'hourly_rate'},
                            {data: 'per_cost', name: 'per_cost'},
                            {data: 'earnings', name: 'earnings'},

                        ],
                        "aaSorting": [2, 'desc'],
                        "iDisplayLength": 15,
                    });

                });

                $('#start_date').on('change', function () {
                    console.log($('#start_date').val());
                    $('#ambassador_kpis').DataTable().ajax.reload();
                });

                $('#end_date').on('change', function () {
                    console.log($('#end_date').val());
                    $('#ambassador_kpis').DataTable().ajax.reload();
                });

                // $.fn.dataTable.ext.errMode = 'none';

                $('#ambassador_kpis')
                    .on('error.dt', function (e, settings, techNote, message) {
                        console.log('An error has been reported by DataTables: ', message);
                    });


            </script>
            <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
            <script src="//cdn.datatables.net/fixedcolumns/3.2.6/js/dataTables.fixedColumns.min.js"></script>
            <script src="//cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
            <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
            <script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
            <script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
            <script src="//cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
        @endpush
    </div>
@endsection
